<?php
/**
 * API Endpoint: Check Wallet Connection
 * Checks if the user has a wallet connected in the current session
 */

// Start session to maintain user state
session_start();

// Set header to indicate JSON response
header('Content-Type: application/json');

// Include database connection
require_once '../includes/db.php';

// Check if wallet is connected in session
$isConnected = isset($_SESSION['wallet_connected']) && $_SESSION['wallet_connected'] === true;
$walletAddress = isset($_SESSION['wallet_address']) ? $_SESSION['wallet_address'] : null;

// If not in session, check database
if (!$isConnected || !$walletAddress) {
    try {
        // Check if user is logged in
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        if ($userId) {
            // User is logged in, check user_wallets
            $stmt = $pdo->prepare("SELECT address, is_connected FROM user_wallets WHERE user_id = ? AND is_connected = 1");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($wallet) {
                $isConnected = true;
                $walletAddress = $wallet['address'];
                
                // Update session
                $_SESSION['wallet_connected'] = true;
                $_SESSION['wallet_address'] = $walletAddress;
            }
        } else {
            // User is not logged in, check guest_wallets
            $stmt = $pdo->prepare("SELECT address, is_connected FROM guest_wallets WHERE session_id = ? AND is_connected = 1");
            $stmt->execute([session_id()]);
            $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($wallet) {
                $isConnected = true;
                $walletAddress = $wallet['address'];
                
                // Update session
                $_SESSION['wallet_connected'] = true;
                $_SESSION['wallet_address'] = $walletAddress;
            }
        }
    } catch (PDOException $e) {
        // Log error
        error_log("Database error in check_connection.php: " . $e->getMessage());
        
        // In case of error, default to not connected
        $isConnected = false;
        $walletAddress = null;
    }
}

// Return response
echo json_encode([
    'connected' => $isConnected,
    'address' => $walletAddress
]);
