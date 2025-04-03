<?php
session_start();
include_once '../includes/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data from request body
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data || !isset($data['campaign_id']) || !isset($data['amount']) || !isset($data['wallet_address']) || !isset($data['transaction_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Get wallet info from session
$walletConnected = false;
$sessionId = session_id();

try {
    $stmt = $pdo->prepare("SELECT * FROM guest_wallets WHERE session_id = ? AND is_connected = TRUE LIMIT 1");
    $stmt->execute([$sessionId]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($wallet) {
        $walletConnected = true;
        
        // Verify the wallet address matches
        if (strtolower($wallet['address']) !== strtolower($data['wallet_address'])) {
            echo json_encode(['success' => false, 'message' => 'Wallet address mismatch']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No connected wallet found']);
        exit;
    }
    
    // Check if campaign exists and is active
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND status = 'approved' LIMIT 1");
    $stmt->execute([$data['campaign_id']]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$campaign) {
        echo json_encode(['success' => false, 'message' => 'Campaign not found or not approved']);
        exit;
    }
    
    // Check if transaction hash already exists
    $stmt = $pdo->prepare("SELECT id FROM contributions WHERE transaction_hash = ? LIMIT 1");
    $stmt->execute([$data['transaction_hash']]);
    $existingTx = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingTx) {
        echo json_encode(['success' => false, 'message' => 'Transaction already processed']);
        exit;
    }
    
    // Insert contribution
    // Use wallet address as identifier rather than wallet database ID 
    // This avoids issues with the foreign key constraints
    $stmt = $pdo->prepare("INSERT INTO contributions 
        (campaign_id, amount, wallet_address, transaction_hash, status, created_at) 
        VALUES (?, ?, ?, ?, 'confirmed', CURRENT_TIMESTAMP)");
    $stmt->execute([
        $data['campaign_id'],
        $data['amount'],
        $data['wallet_address'],
        $data['transaction_hash']
    ]);
    
    $contributionId = $pdo->lastInsertId();
    
    // If staking is enabled and requested
    if ($campaign['staking_enabled'] && isset($data['staking']) && $data['staking']) {
        // Validate minimum stake amount
        if (floatval($data['amount']) < floatval($campaign['min_stake_amount'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'Amount below minimum staking requirement of ' . $campaign['min_stake_amount']
            ]);
            exit;
        }
        
        // Use custom staking period if provided, otherwise use the campaign default
        $stakeDurationDays = isset($data['staking_period']) ? intval($data['staking_period']) : intval($campaign['staking_duration_days']);
        
        // Validate staking period
        if ($stakeDurationDays < 30 || $stakeDurationDays > 365) {
            echo json_encode([
                'success' => false, 
                'message' => 'Staking period must be between 30 and 365 days'
            ]);
            exit;
        }
        
        // Calculate stake end date
        $endDate = date('Y-m-d H:i:s', strtotime("+{$stakeDurationDays} days"));
        
        // Insert stake
        $stmt = $pdo->prepare("INSERT INTO stakes 
            (contribution_id, wallet_address, amount, apy, start_date, end_date, 
             blockchain_network, status, transaction_hash, created_at, updated_at) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, 'active', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $stmt->execute([
            $contributionId,
            $data['wallet_address'],
            $data['amount'],
            $campaign['staking_apy'],
            $endDate,
            $campaign['blockchain_network'],
            $data['transaction_hash']
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Stake successful', 
            'contribution_id' => $contributionId,
            'stake_id' => $pdo->lastInsertId(),
            'staking_period' => $stakeDurationDays,
            'end_date' => $endDate
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => 'Contribution successful', 
            'contribution_id' => $contributionId
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in contribute.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    error_log("Error in contribute.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing request: ' . $e->getMessage()]);
    exit;
}