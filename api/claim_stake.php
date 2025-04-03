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

if (!$data || !isset($data['stake_id'])) {
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
    
    if (!$wallet) {
        echo json_encode(['success' => false, 'message' => 'No connected wallet found']);
        exit;
    }
    
    $walletAddress = $wallet['address'];
    
    // Get stake details
    $stmt = $pdo->prepare("
        SELECT s.*, c.campaign_id, c.amount as contribution_amount, 
               cam.title as campaign_title, cam.funding_goal, cam.token_symbol
        FROM stakes s
        JOIN contributions c ON s.contribution_id = c.id
        JOIN campaigns cam ON c.campaign_id = cam.id
        WHERE s.id = ? AND s.wallet_address = ? LIMIT 1
    ");
    $stmt->execute([$data['stake_id'], $walletAddress]);
    $stake = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stake) {
        echo json_encode(['success' => false, 'message' => 'Stake not found or not owned by connected wallet']);
        exit;
    }
    
    // Check if stake is active and ended
    $now = new DateTime();
    $endDate = new DateTime($stake['end_date']);
    
    if ($stake['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'This stake is not active or has already been claimed']);
        exit;
    }
    
    if ($now < $endDate && !isset($data['simulation'])) {
        echo json_encode(['success' => false, 'message' => 'Staking period has not ended yet']);
        exit;
    }
    
    // Calculate rewards
    $amount = floatval($stake['amount']);
    $apy = floatval($stake['apy']);
    $startDate = new DateTime($stake['start_date']);
    $totalDays = $startDate->diff($endDate)->days;
    
    $dailyRate = $apy / 365 / 100;
    $reward = $amount * $dailyRate * $totalDays;
    
    // Generate transaction hash if not provided (for simulation purposes)
    $transactionHash = $data['transaction_hash'] ?? '0x' . bin2hex(random_bytes(32));
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Update stake status to claimed
    $stmt = $pdo->prepare("
        UPDATE stakes 
        SET status = 'claimed', 
            claim_transaction_hash = ?, 
            claimed_at = CURRENT_TIMESTAMP,
            reward_amount = ?
        WHERE id = ?
    ");
    $stmt->execute([$transactionHash, $reward, $data['stake_id']]);
    
    // Record reward disbursement
    $stmt = $pdo->prepare("
        INSERT INTO rewards (
            stake_id, 
            wallet_address, 
            campaign_id, 
            amount, 
            transaction_hash, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([
        $data['stake_id'],
        $walletAddress,
        $stake['campaign_id'],
        $reward,
        $transactionHash
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Stake rewards claimed successfully',
        'stake_id' => $data['stake_id'],
        'reward_amount' => $reward,
        'transaction_hash' => $transactionHash
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Database error in claim_stake.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error in claim_stake.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing request: ' . $e->getMessage()]);
    exit;
}