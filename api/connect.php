<?php
/**
 * API Endpoint: Connect Wallet
 * Saves the user's wallet address to the database
 */

// Start session to maintain user state
session_start();

// Set header to indicate JSON response
header('Content-Type: application/json');

// Include database connection
require_once '../includes/db.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST is allowed.'
    ]);
    exit;
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

// Validate the address
if (!isset($data['address']) || empty($data['address'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Wallet address is required.'
    ]);
    exit;
}

// Sanitize the wallet address
$address = trim($data['address']);

// Validate Ethereum address format (0x followed by 40 hex characters)
if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid Ethereum address format.'
    ]);
    exit;
}

try {
    // Check if this user already has a wallet connected
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if ($userId) {
        // User is logged in, check for existing wallet
        $stmt = $pdo->prepare("SELECT id, address FROM user_wallets WHERE user_id = ?");
        $stmt->execute([$userId]);
        $existingWallet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingWallet) {
            // User already has a wallet, update it
            $stmt = $pdo->prepare("UPDATE user_wallets SET address = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
            $stmt->execute([$address, $userId]);
            
            $message = 'Wallet address updated successfully.';
        } else {
            // User doesn't have a wallet yet, insert new one
            $stmt = $pdo->prepare("INSERT INTO user_wallets (user_id, address, created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            $stmt->execute([$userId, $address]);
            
            $message = 'Wallet address saved successfully.';
        }
    } else {
        // User is not logged in, store in session
        $_SESSION['wallet_address'] = $address;
        
        // Check if this address is already in guest_wallets - if so, update its session and connection status
        $stmt = $pdo->prepare("
            SELECT id, session_id FROM guest_wallets WHERE address = ? LIMIT 1
        ");
        $stmt->execute([$address]);
        $existingWallet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingWallet) {
            // This wallet already exists, update its session_id and set it as connected
            $stmt = $pdo->prepare("
                UPDATE guest_wallets 
                SET session_id = ?, 
                    is_connected = TRUE, 
                    updated_at = CURRENT_TIMESTAMP
                WHERE address = ?
            ");
            $stmt->execute([session_id(), $address]);
        } else {
            // This is a new wallet address, insert it
            $stmt = $pdo->prepare("
                INSERT INTO guest_wallets (address, session_id, is_connected, created_at, updated_at) 
                VALUES (?, ?, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON CONFLICT (session_id) 
                DO UPDATE SET 
                    address = EXCLUDED.address,
                    is_connected = TRUE,
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$address, session_id()]);
        }
        
        $message = 'Wallet address saved successfully.';
    }
    
    // Store in session for convenience
    $_SESSION['wallet_address'] = $address;
    $_SESSION['wallet_connected'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'address' => $address
    ]);
    
} catch (PDOException $e) {
    // Log error (but don't expose details to client)
    error_log("Database error in connect.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while saving your wallet address. Please try again.'
    ]);
}
