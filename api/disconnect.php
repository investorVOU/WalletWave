<?php
/**
 * API Endpoint: Disconnect Wallet
 * Removes the user's wallet address from the session and database
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

try {
    // Check if user is logged in
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if ($userId) {
        // User is logged in, update database
        // Note: We don't delete the record, just set a flag to indicate disconnection
        $stmt = $pdo->prepare("UPDATE user_wallets SET is_connected = FALSE, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        // User is not logged in, update guest_wallets
        if (isset($_SESSION['wallet_address'])) {
            $stmt = $pdo->prepare("UPDATE guest_wallets SET is_connected = FALSE, updated_at = CURRENT_TIMESTAMP WHERE session_id = ?");
            $stmt->execute([session_id()]);
        }
    }
    
    // Remove from session
    unset($_SESSION['wallet_address']);
    unset($_SESSION['wallet_connected']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Wallet disconnected successfully.'
    ]);
    
} catch (PDOException $e) {
    // Log error (but don't expose details to client)
    error_log("Database error in disconnect.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while disconnecting your wallet. Please try again.'
    ]);
}
