<?php
/**
 * API Endpoint: Check Wallet Connection
 * Checks if the user has a wallet connected in the current session
 */

// Start session to maintain user state
session_start();

// Set header to indicate JSON response
header('Content-Type: application/json');

// Set defaults
$isConnected = false;
$walletAddress = null;
$error = null;

try {
    // Include database connection
    require_once '../includes/db.php';
    
    // First check if wallet is connected in session (fastest way)
    if (isset($_SESSION['wallet_connected']) && $_SESSION['wallet_connected'] === true && 
        isset($_SESSION['wallet_address']) && $_SESSION['wallet_address']) {
        
        $isConnected = true;
        $walletAddress = $_SESSION['wallet_address'];
        
        // Validate the Ethereum address format
        if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $walletAddress)) {
            // Invalid Ethereum address format - reset
            $isConnected = false;
            $walletAddress = null;
            
            // Clear invalid session data
            unset($_SESSION['wallet_connected']);
            unset($_SESSION['wallet_address']);
        }
    }
    
    // If not in session or invalid, check database
    if (!$isConnected || !$walletAddress) {
        // Check if user is logged in
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        if ($userId) {
            // User is logged in, check user_wallets
            $stmt = $pdo->prepare("SELECT address, is_connected FROM user_wallets WHERE user_id = ? AND is_connected = true ORDER BY last_connected DESC LIMIT 1");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($wallet && isset($wallet['address']) && $wallet['address']) {
                $isConnected = true;
                $walletAddress = $wallet['address'];
                
                // Update session
                $_SESSION['wallet_connected'] = true;
                $_SESSION['wallet_address'] = $walletAddress;
            }
        } else {
            // User is not logged in, check guest_wallets
            $sessionId = session_id();
            if ($sessionId) {
                $stmt = $pdo->prepare("SELECT address, is_connected FROM guest_wallets WHERE session_id = ? AND is_connected = true ORDER BY last_connected DESC LIMIT 1");
                $stmt->execute([$sessionId]);
                $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($wallet && isset($wallet['address']) && $wallet['address']) {
                    $isConnected = true;
                    $walletAddress = $wallet['address'];
                    
                    // Update session
                    $_SESSION['wallet_connected'] = true;
                    $_SESSION['wallet_address'] = $walletAddress;
                }
            }
        }
    }
} catch (PDOException $e) {
    // Log database error
    error_log("Database error in check_connection.php: " . $e->getMessage());
    $error = "Database error occurred";
    
    // In case of error, default to not connected
    $isConnected = false;
    $walletAddress = null;
} catch (Exception $e) {
    // Log general error
    error_log("Error in check_connection.php: " . $e->getMessage());
    $error = "An error occurred while checking connection";
    
    // In case of error, default to not connected
    $isConnected = false;
    $walletAddress = null;
}

// Return response
$response = [
    'success' => true,
    'connected' => $isConnected,
    'address' => $walletAddress
];

// Add error info if any
if ($error) {
    $response['success'] = false;
    $response['error'] = $error;
}

echo json_encode($response);
