<?php
session_start();
include_once 'includes/db.php';

// Check if user has a connected wallet
$walletConnected = false;
$walletAddress = '';
$walletData = null;
$userTransactions = [];
$userStakes = [];
$userCampaigns = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM guest_wallets WHERE session_id = ? AND is_connected = TRUE LIMIT 1");
    $stmt->execute([session_id()]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($wallet) {
        $walletConnected = true;
        $walletAddress = $wallet['address'];
        $walletData = $wallet;
        
        // Get user's transactions
        $stmt = $pdo->prepare("
            SELECT c.*, cam.title as campaign_title, cam.thumbnail_url, bn.name as network_name, 
                   bn.short_name as network_short, bn.block_explorer_url
            FROM contributions c
            LEFT JOIN campaigns cam ON c.campaign_id = cam.id
            LEFT JOIN blockchain_networks bn ON cam.blockchain_network = bn.chain_id
            WHERE c.wallet_address = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$walletAddress]);
        $userTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get user's stakes
        $stmt = $pdo->prepare("
            SELECT s.*, c.campaign_id, c.amount as contribution_amount, 
                   cam.title as campaign_title, bn.name as network_name,
                   bn.block_explorer_url
            FROM stakes s
            JOIN contributions c ON s.contribution_id = c.id
            JOIN campaigns cam ON c.campaign_id = cam.id
            LEFT JOIN blockchain_networks bn ON cam.blockchain_network = bn.chain_id
            WHERE s.wallet_address = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$walletAddress]);
        $userStakes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get user's created campaigns
        $stmt = $pdo->prepare("
            SELECT * FROM campaigns WHERE creator_address = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$walletAddress]);
        $userCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Calculate totals
$totalContributed = 0;
$totalStaked = 0;
$activeStakes = 0;
$pendingRewards = 0;

foreach ($userTransactions as $tx) {
    $totalContributed += floatval($tx['amount']);
}

foreach ($userStakes as $stake) {
    if ($stake['status'] === 'active') {
        $totalStaked += floatval($stake['amount']);
        $activeStakes++;
        
        // Calculate pending rewards
        $apy = floatval($stake['apy']);
        $amount = floatval($stake['amount']);
        $startDate = new DateTime($stake['start_date']);
        $endDate = new DateTime($stake['end_date']);
        $now = new DateTime();
        
        // If the stake is still active
        if ($now < $endDate) {
            $daysPassed = $startDate->diff($now)->days;
            $totalDays = $startDate->diff($endDate)->days;
            
            if ($totalDays > 0) {
                // Calculate accrued rewards based on days passed
                $dailyRate = $apy / 365 / 100;
                $pendingRewards += $amount * $dailyRate * $daysPassed;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile - CryptoFund</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="bg-gradient-to-r from-slate-900 to-slate-800 text-white min-h-screen">
    <?php include_once 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <?php if (!$walletConnected): ?>
        <!-- Wallet Connection Required -->
        <div class="bg-slate-800/80 rounded-xl p-10 border border-blue-500/20 shadow-lg text-center max-w-3xl mx-auto animate__animated animate__fadeIn">
            <div class="text-6xl text-blue-400 mb-6">
                <i class="fas fa-wallet"></i>
            </div>
            <h2 class="text-2xl font-bold mb-3">Connect Your Wallet First</h2>
            <p class="text-gray-400 mb-8">You need to connect your wallet to view your profile.</p>
            <button id="connectWalletBtn" class="connect-wallet-btn px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2 mx-auto">
                <i class="fas fa-plug"></i> Connect Wallet
            </button>
        </div>
        <?php else: ?>
        <!-- Profile Header -->
        <div class="mb-10">
            <h1 class="text-3xl font-bold mb-2 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">
                Your Profile
            </h1>
            <p class="text-gray-400">Manage your wallet and view your activity.</p>
        </div>
        
        <!-- Profile Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10">
            <!-- Wallet Info Card -->
            <div class="bg-slate-800/50 rounded-xl p-6 border border-blue-500/20 shadow-lg">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold">Wallet</h2>
                    <span class="bg-green-500/20 text-green-400 text-xs py-1 px-2 rounded-full">Connected</span>
                </div>
                <div class="bg-slate-900/80 rounded-lg p-4 mb-4 break-all">
                    <p class="text-blue-400 font-mono text-sm"><?php echo $walletAddress; ?></p>
                </div>
                <div class="flex justify-between text-sm text-gray-400 mb-2">
                    <span>Connected Since:</span>
                    <span><?php echo date('M j, Y', strtotime($walletData['created_at'])); ?></span>
                </div>
                <div class="mt-4">
                    <button id="disconnectWalletBtn" class="w-full py-2 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 font-medium transition border border-red-500/20">
                        Disconnect Wallet
                    </button>
                </div>
            </div>
            
            <!-- Activity Summary Cards -->
            <div class="bg-slate-800/50 rounded-xl p-6 border border-purple-500/20 shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-purple-500/20 rounded-full h-10 w-10 flex items-center justify-center">
                        <i class="fas fa-hand-holding-usd text-purple-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Total Contributed</h3>
                        <p class="text-2xl font-bold text-purple-400"><?php echo number_format($totalContributed, 4); ?></p>
                    </div>
                </div>
                <div class="text-sm text-gray-400">
                    <p>Across <?php echo count($userTransactions); ?> transactions</p>
                </div>
            </div>
            
            <div class="bg-slate-800/50 rounded-xl p-6 border border-blue-500/20 shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-blue-500/20 rounded-full h-10 w-10 flex items-center justify-center">
                        <i class="fas fa-layer-group text-blue-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Total Staked</h3>
                        <p class="text-2xl font-bold text-blue-400"><?php echo number_format($totalStaked, 4); ?></p>
                    </div>
                </div>
                <div class="text-sm text-gray-400">
                    <p><?php echo $activeStakes; ?> active stake<?php echo $activeStakes !== 1 ? 's' : ''; ?></p>
                </div>
            </div>
            
            <div class="bg-slate-800/50 rounded-xl p-6 border border-green-500/20 shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-green-500/20 rounded-full h-10 w-10 flex items-center justify-center">
                        <i class="fas fa-coins text-green-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Pending Rewards</h3>
                        <p class="text-2xl font-bold text-green-400"><?php echo number_format($pendingRewards, 6); ?></p>
                    </div>
                </div>
                <div class="text-sm text-gray-400">
                    <p>From your active stakes</p>
                </div>
            </div>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="mb-6 border-b border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="profileTabs" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg active border-blue-500 text-blue-400" 
                            id="transactions-tab" data-tab="transactions" type="button" role="tab">
                        Transactions
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:border-gray-500 hover:text-gray-300" 
                            id="stakes-tab" data-tab="stakes" type="button" role="tab">
                        Staking
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:border-gray-500 hover:text-gray-300" 
                            id="campaigns-tab" data-tab="campaigns" type="button" role="tab">
                        Your Campaigns
                    </button>
                </li>
            </ul>
        </div>
        
        <!-- Tab Content -->
        <div id="tabContent">
            <!-- Transactions Tab -->
            <div id="transactions" class="tab-panel active animate__animated animate__fadeIn">
                <?php if (empty($userTransactions)): ?>
                <div class="bg-slate-800/50 rounded-xl p-8 border border-blue-500/20 shadow-lg text-center">
                    <div class="text-5xl text-gray-600 mb-4">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">No Transactions Yet</h3>
                    <p class="text-gray-400 mb-4">You haven't made any contributions to campaigns yet.</p>
                    <a href="campaigns.php" class="inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 rounded-lg text-white font-medium">
                        <i class="fas fa-search mr-2"></i> Explore Campaigns
                    </a>
                </div>
                <?php else: ?>
                <div class="bg-slate-800/50 rounded-xl overflow-hidden border border-blue-500/20 shadow-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-900/50 text-gray-400 uppercase">
                                <tr>
                                    <th class="px-6 py-3 text-left">Date</th>
                                    <th class="px-6 py-3 text-left">Campaign</th>
                                    <th class="px-6 py-3 text-left">Amount</th>
                                    <th class="px-6 py-3 text-left">Type</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-left">Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userTransactions as $tx): ?>
                                <tr class="border-b border-gray-700 hover:bg-slate-700/30">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('M j, Y H:i', strtotime($tx['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($tx['thumbnail_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($tx['thumbnail_url']); ?>" alt="Campaign" class="w-8 h-8 rounded-md object-cover">
                                            <?php else: ?>
                                            <div class="w-8 h-8 rounded-md bg-slate-700 flex items-center justify-center">
                                                <i class="fas fa-image text-slate-500"></i>
                                            </div>
                                            <?php endif; ?>
                                            <a href="campaigns.php?id=<?php echo $tx['campaign_id']; ?>" class="hover:text-blue-400 transition">
                                                <?php echo htmlspecialchars($tx['campaign_title'] ?? 'Unknown Campaign'); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold">
                                        <?php echo number_format($tx['amount'], 4); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (isset($tx['staking']) && $tx['staking']): ?>
                                        <span class="bg-purple-500/20 text-purple-400 text-xs py-1 px-2 rounded-full">
                                            Staking
                                        </span>
                                        <?php else: ?>
                                        <span class="bg-blue-500/20 text-blue-400 text-xs py-1 px-2 rounded-full">
                                            Contribution
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $statusClass = '';
                                        switch ($tx['status']) {
                                            case 'confirmed':
                                                $statusClass = 'bg-green-500/20 text-green-400';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-yellow-500/20 text-yellow-400';
                                                break;
                                            case 'failed':
                                                $statusClass = 'bg-red-500/20 text-red-400';
                                                break;
                                            default:
                                                $statusClass = 'bg-gray-500/20 text-gray-400';
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?> text-xs py-1 px-2 rounded-full">
                                            <?php echo ucfirst($tx['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-400">
                                        <?php if (!empty($tx['transaction_hash']) && !empty($tx['block_explorer_url'])): ?>
                                        <a href="<?php echo $tx['block_explorer_url']; ?>/tx/<?php echo $tx['transaction_hash']; ?>" 
                                           target="_blank" rel="noopener noreferrer"
                                           class="text-gray-400 hover:text-blue-400 transition">
                                            <?php echo substr($tx['transaction_hash'], 0, 6) . '...' . substr($tx['transaction_hash'], -4); ?>
                                            <i class="fas fa-external-link-alt text-xs ml-1"></i>
                                        </a>
                                        <?php elseif (!empty($tx['transaction_hash'])): ?>
                                        <span class="font-mono">
                                            <?php echo substr($tx['transaction_hash'], 0, 6) . '...' . substr($tx['transaction_hash'], -4); ?>
                                        </span>
                                        <?php else: ?>
                                        <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Stakes Tab -->
            <div id="stakes" class="tab-panel hidden animate__animated animate__fadeIn">
                <?php if (empty($userStakes)): ?>
                <div class="bg-slate-800/50 rounded-xl p-8 border border-blue-500/20 shadow-lg text-center">
                    <div class="text-5xl text-gray-600 mb-4">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">No Stakes Yet</h3>
                    <p class="text-gray-400 mb-4">You haven't staked in any campaigns yet.</p>
                    <a href="campaigns.php" class="inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 rounded-lg text-white font-medium">
                        <i class="fas fa-search mr-2"></i> Find Staking Opportunities
                    </a>
                </div>
                <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($userStakes as $stake): 
                        // Calculate stake progress
                        $startDate = new DateTime($stake['start_date']);
                        $endDate = new DateTime($stake['end_date']);
                        $now = new DateTime();
                        
                        $totalDays = $startDate->diff($endDate)->days;
                        $daysPassed = $startDate->diff($now)->days;
                        $percentComplete = min(100, max(0, ($daysPassed / max(1, $totalDays)) * 100));
                        
                        // Calculate expected rewards
                        $amount = floatval($stake['amount']);
                        $apy = floatval($stake['apy']);
                        $dailyRate = $apy / 365 / 100;
                        $totalReward = $amount * $dailyRate * $totalDays;
                        $earnedReward = $amount * $dailyRate * $daysPassed;
                    ?>
                    <div class="bg-slate-800/50 rounded-xl p-6 border border-purple-500/20 shadow-lg">
                        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
                            <div>
                                <h3 class="text-xl font-semibold mb-1"><?php echo htmlspecialchars($stake['campaign_title'] ?? 'Campaign Stake'); ?></h3>
                                <div class="flex items-center gap-2 text-sm text-gray-400">
                                    <span>Staked on <?php echo date('M j, Y', strtotime($stake['created_at'])); ?></span>
                                    <span>•</span>
                                    <span><?php echo htmlspecialchars($stake['network_name'] ?? 'Unknown Network'); ?></span>
                                </div>
                            </div>
                            <div>
                                <?php 
                                $statusClass = '';
                                switch ($stake['status']) {
                                    case 'active':
                                        $statusClass = 'bg-green-500/20 text-green-400';
                                        break;
                                    case 'completed':
                                        $statusClass = 'bg-blue-500/20 text-blue-400';
                                        break;
                                    case 'claimed':
                                        $statusClass = 'bg-purple-500/20 text-purple-400';
                                        break;
                                    default:
                                        $statusClass = 'bg-gray-500/20 text-gray-400';
                                }
                                ?>
                                <span class="<?php echo $statusClass; ?> text-sm py-1.5 px-3 rounded-full font-medium">
                                    <?php echo ucfirst($stake['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="bg-slate-900/50 p-4 rounded-lg">
                                <p class="text-sm text-gray-400 mb-1">Staked Amount</p>
                                <p class="text-lg font-semibold"><?php echo number_format($stake['amount'], 4); ?></p>
                            </div>
                            <div class="bg-slate-900/50 p-4 rounded-lg">
                                <p class="text-sm text-gray-400 mb-1">APY</p>
                                <p class="text-lg font-semibold text-green-400"><?php echo number_format($stake['apy'], 2); ?>%</p>
                            </div>
                            <div class="bg-slate-900/50 p-4 rounded-lg">
                                <p class="text-sm text-gray-400 mb-1">Lock Period</p>
                                <p class="text-lg font-semibold"><?php echo $totalDays; ?> days</p>
                            </div>
                            <div class="bg-slate-900/50 p-4 rounded-lg">
                                <p class="text-sm text-gray-400 mb-1">Ends On</p>
                                <p class="text-lg font-semibold"><?php echo date('M j, Y', strtotime($stake['end_date'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between mb-1 text-sm">
                                <span>Staking Progress</span>
                                <span><?php echo number_format($percentComplete, 1); ?>%</span>
                            </div>
                            <div class="h-2 w-full bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-600 to-purple-600" style="width: <?php echo $percentComplete; ?>%"></div>
                            </div>
                            <div class="flex justify-between mt-1 text-xs text-gray-400">
                                <span><?php echo date('M j, Y', strtotime($stake['start_date'])); ?></span>
                                <span><?php echo date('M j, Y', strtotime($stake['end_date'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="bg-slate-900/30 p-4 rounded-lg border border-purple-500/10 mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-400">Expected Reward</span>
                                <span class="text-lg font-semibold text-purple-400"><?php echo number_format($totalReward, 6); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-400">Earned So Far</span>
                                <span class="text-lg font-semibold text-green-400"><?php echo number_format($earnedReward, 6); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($stake['status'] === 'active' && $now > $endDate): ?>
                        <div class="flex justify-end">
                            <button class="claim-rewards-btn py-2 px-4 rounded-lg bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium transition shadow-md"
                                    data-stake-id="<?php echo $stake['id']; ?>"
                                    data-campaign-id="<?php echo $stake['campaign_id']; ?>">
                                <i class="fas fa-coins mr-2"></i> Claim Rewards
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Campaigns Tab -->
            <div id="campaigns" class="tab-panel hidden animate__animated animate__fadeIn">
                <?php if (empty($userCampaigns)): ?>
                <div class="bg-slate-800/50 rounded-xl p-8 border border-blue-500/20 shadow-lg text-center">
                    <div class="text-5xl text-gray-600 mb-4">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">No Campaigns Created</h3>
                    <p class="text-gray-400 mb-4">You haven't created any fundraising campaigns yet.</p>
                    <a href="create-campaign.php" class="inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 rounded-lg text-white font-medium">
                        <i class="fas fa-plus mr-2"></i> Create Campaign
                    </a>
                </div>
                <?php else: ?>
                <div class="flex justify-end mb-4">
                    <a href="create-campaign.php" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 rounded-lg text-white font-medium text-sm">
                        <i class="fas fa-plus mr-2"></i> New Campaign
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($userCampaigns as $campaign): 
                        // Calculate days left
                        $endDate = new DateTime($campaign['end_date']);
                        $now = new DateTime();
                        $daysLeft = max(0, $now->diff($endDate)->days);
                        
                        // Calculate progress
                        $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM contributions WHERE campaign_id = ? AND status = 'confirmed'");
                        $stmt->execute([$campaign['id']]);
                        $totalContributions = $stmt->fetchColumn() ?: 0;
                        $progressPercent = min(100, round(($totalContributions / max(1, $campaign['funding_goal'])) * 100));
                    ?>
                    <div class="bg-slate-800/50 rounded-xl overflow-hidden border border-blue-500/20 shadow-lg animate__animated animate__fadeIn campaign-card">
                        <?php if (!empty($campaign['thumbnail_url'])): ?>
                        <img src="<?php echo htmlspecialchars($campaign['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($campaign['title']); ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-r from-slate-700 to-slate-600 flex items-center justify-center">
                            <i class="fas fa-image text-4xl text-slate-500"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="bg-blue-600/20 text-blue-400 px-2 py-1 rounded-full text-xs font-medium">
                                    <?php echo htmlspecialchars($campaign['category'] ?? 'General'); ?>
                                </span>
                                
                                <?php 
                                $statusClass = '';
                                switch ($campaign['status']) {
                                    case 'approved':
                                        $statusClass = 'bg-green-600/20 text-green-400';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-yellow-600/20 text-yellow-400';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'bg-red-600/20 text-red-400';
                                        break;
                                    default:
                                        $statusClass = 'bg-gray-600/20 text-gray-400';
                                }
                                ?>
                                <span class="<?php echo $statusClass; ?> px-2 py-1 rounded-full text-xs font-medium">
                                    <?php echo ucfirst($campaign['status']); ?>
                                </span>
                                
                                <?php if ($campaign['staking_enabled']): ?>
                                <span class="bg-purple-600/20 text-purple-400 px-2 py-1 rounded-full text-xs font-medium">
                                    Staking Enabled
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($campaign['title']); ?></h3>
                            
                            <p class="text-gray-400 text-sm mb-4 line-clamp-2">
                                <?php echo htmlspecialchars($campaign['description']); ?>
                            </p>
                            
                            <div class="mb-4">
                                <div class="h-2 w-full bg-gray-700 rounded-full">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" 
                                         style="width: <?php echo $progressPercent; ?>%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-blue-400 font-semibold">
                                        <?php echo number_format($totalContributions, 2); ?> 
                                        <?php echo htmlspecialchars($campaign['token_symbol']); ?>
                                    </span>
                                    <span class="text-gray-400">
                                        of <?php echo number_format($campaign['funding_goal'], 2); ?> 
                                        <?php echo htmlspecialchars($campaign['token_symbol']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 mb-5 text-center">
                                <div class="bg-slate-900/50 rounded-lg p-2">
                                    <p class="text-xs text-gray-400">Created</p>
                                    <p class="font-semibold"><?php echo date('M j, Y', strtotime($campaign['created_at'])); ?></p>
                                </div>
                                <div class="bg-slate-900/50 rounded-lg p-2">
                                    <p class="text-xs text-gray-400">
                                        <?php echo $now > $endDate ? 'Ended' : 'Ends In'; ?>
                                    </p>
                                    <p class="font-semibold <?php echo $daysLeft < 3 ? 'text-red-400' : ''; ?>">
                                        <?php echo $now > $endDate ? 'Completed' : $daysLeft . ' days'; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="campaigns.php?id=<?php echo $campaign['id']; ?>" 
                                   class="flex-1 py-2 text-center rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-sm font-medium transition">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <?php if ($campaign['status'] === 'approved'): ?>
                                <a href="edit-campaign.php?id=<?php echo $campaign['id']; ?>" 
                                   class="flex-1 py-2 text-center rounded-lg bg-slate-700/50 hover:bg-slate-700 text-white text-sm font-medium transition">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <?php include_once 'includes/footer.php'; ?>
    
    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50"></div>
    
    <!-- Claims Processing Modal -->
    <div id="claimModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-75 backdrop-blur-sm"></div>
        <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 border border-green-500/30">
            <div id="claimProcessing" class="text-center py-6">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-green-500 mx-auto mb-4"></div>
                <h3 class="text-xl font-semibold mb-2">Processing Claim</h3>
                <p class="text-gray-400">Please confirm the transaction in your wallet and wait for it to be processed...</p>
            </div>
            
            <div id="claimSuccess" class="text-center py-6 hidden">
                <div class="bg-green-500/20 text-green-400 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Rewards Claimed!</h3>
                <p class="text-gray-400 mb-4">Your staking rewards have been successfully claimed.</p>
                <button id="claimSuccessBtn" class="w-full py-3 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold shadow-lg transition duration-300">
                    Close
                </button>
            </div>
            
            <div id="claimError" class="text-center py-6 hidden">
                <div class="bg-red-500/20 text-red-400 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Claim Failed</h3>
                <p id="claimErrorMessage" class="text-gray-400 mb-6">There was an error processing your claim. Please try again.</p>
                <button id="retryClaimBtn" class="w-full py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300">
                    Try Again
                </button>
                <button id="closeClaimErrorBtn" class="w-full py-3 mt-3 rounded-lg bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-800 font-semibold transition duration-300">
                    Cancel
                </button>
            </div>
            
            <button id="closeClaimModalBtn" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <!-- Web3 Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.4/dist/web3.min.js"></script>
    <script src="https://cdn.ethers.io/lib/ethers-5.6.umd.min.js"></script>
    
    <!-- Custom JS files -->
    <script src="js/wallet.js"></script>
    <script src="js/contract.js"></script>
    <script src="js/app.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize wallet connection
            if (typeof initWalletConnection === 'function') {
                initWalletConnection();
            }
            
            // Setup tabs
            const tabs = document.querySelectorAll('#profileTabs button');
            const tabPanels = document.querySelectorAll('.tab-panel');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Reset all tabs and panels
                    tabs.forEach(t => {
                        t.classList.remove('active', 'border-blue-500', 'text-blue-400');
                        t.classList.add('border-transparent', 'hover:border-gray-500', 'hover:text-gray-300');
                    });
                    
                    tabPanels.forEach(panel => {
                        panel.classList.add('hidden');
                        panel.classList.remove('active');
                    });
                    
                    // Activate selected tab and panel
                    tab.classList.add('active', 'border-blue-500', 'text-blue-400');
                    tab.classList.remove('border-transparent', 'hover:border-gray-500', 'hover:text-gray-300');
                    
                    const panelId = tab.getAttribute('data-tab');
                    const panel = document.getElementById(panelId);
                    if (panel) {
                        panel.classList.remove('hidden');
                        panel.classList.add('active', 'animate__animated', 'animate__fadeIn');
                    }
                });
            });
            
            // Handle disconnect wallet button
            const disconnectBtn = document.getElementById('disconnectWalletBtn');
            if (disconnectBtn) {
                disconnectBtn.addEventListener('click', async () => {
                    if (typeof disconnectWallet === 'function') {
                        try {
                            await disconnectWallet();
                            window.location.reload();
                        } catch (error) {
                            console.error('Error disconnecting wallet:', error);
                            showToast('Failed to disconnect wallet. Please try again.', 'error');
                        }
                    }
                });
            }
            
            // Handle claim rewards buttons
            const claimButtons = document.querySelectorAll('.claim-rewards-btn');
            const claimModal = document.getElementById('claimModal');
            const claimProcessing = document.getElementById('claimProcessing');
            const claimSuccess = document.getElementById('claimSuccess');
            const claimError = document.getElementById('claimError');
            const claimErrorMessage = document.getElementById('claimErrorMessage');
            
            claimButtons.forEach(btn => {
                btn.addEventListener('click', async () => {
                    const stakeId = btn.getAttribute('data-stake-id');
                    const campaignId = btn.getAttribute('data-campaign-id');
                    
                    // Show claim modal
                    claimModal.classList.remove('hidden');
                    claimProcessing.classList.remove('hidden');
                    claimSuccess.classList.add('hidden');
                    claimError.classList.add('hidden');
                    
                    try {
                        // Check if wallet is connected
                        if (!selectedAccount) {
                            throw new Error('Wallet not connected');
                        }
                        
                        // Initialize contract if needed
                        if (typeof initializeContract === 'function' && !cryptoFundContract) {
                            await initializeContract();
                        }
                        
                        // Call the contract function if available
                        if (typeof cryptoFundContract !== 'undefined' && cryptoFundContract !== null) {
                            try {
                                // Call the claim function on the contract
                                const tx = await cryptoFundContract.claimStakingReward(campaignId);
                                const receipt = await tx.wait();
                                
                                // Update the database to mark the stake as claimed
                                const response = await fetch('/api/claim_stake.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        stake_id: stakeId,
                                        transaction_hash: receipt.transactionHash
                                    }),
                                });
                                
                                const data = await response.json();
                                
                                if (!data.success) {
                                    throw new Error(data.message || 'Failed to record claim');
                                }
                                
                                // Show success
                                claimProcessing.classList.add('hidden');
                                claimSuccess.classList.remove('hidden');
                                
                            } catch (error) {
                                console.error('Contract claim error:', error);
                                throw new Error(error.message || 'Failed to execute claim transaction');
                            }
                        } else {
                            // Simulate claim if contract is not available
                            await new Promise(resolve => setTimeout(resolve, 2000));
                            
                            // Make API call to claim stake
                            const response = await fetch('/api/claim_stake.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    stake_id: stakeId,
                                    simulation: true
                                }),
                            });
                            
                            const data = await response.json();
                            
                            if (!data.success) {
                                throw new Error(data.message || 'Failed to claim rewards');
                            }
                            
                            // Show success
                            claimProcessing.classList.add('hidden');
                            claimSuccess.classList.remove('hidden');
                        }
                        
                    } catch (error) {
                        console.error('Claim error:', error);
                        
                        // Show error state
                        claimProcessing.classList.add('hidden');
                        claimError.classList.remove('hidden');
                        claimErrorMessage.textContent = error.message || 'Transaction failed';
                    }
                });
            });
            
            // Claim modal close buttons
            document.getElementById('claimSuccessBtn').addEventListener('click', () => {
                claimModal.classList.add('hidden');
                window.location.reload(); // Reload to show updated state
            });
            
            document.getElementById('closeClaimErrorBtn').addEventListener('click', () => {
                claimModal.classList.add('hidden');
            });
            
            document.getElementById('closeClaimModalBtn').addEventListener('click', () => {
                claimModal.classList.add('hidden');
            });
            
            // Show toast notification
            function showToast(message, type = 'info') {
                const toastContainer = document.getElementById('toastContainer');
                const toast = document.createElement('div');
                
                let bgColor, textColor, icon;
                switch (type) {
                    case 'success':
                        bgColor = 'bg-green-500/90';
                        textColor = 'text-white';
                        icon = '<i class="fas fa-check-circle mr-2"></i>';
                        break;
                    case 'error':
                        bgColor = 'bg-red-500/90';
                        textColor = 'text-white';
                        icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
                        break;
                    case 'warning':
                        bgColor = 'bg-yellow-500/90';
                        textColor = 'text-white';
                        icon = '<i class="fas fa-exclamation-triangle mr-2"></i>';
                        break;
                    default:
                        bgColor = 'bg-blue-500/90';
                        textColor = 'text-white';
                        icon = '<i class="fas fa-info-circle mr-2"></i>';
                }
                
                toast.className = `${bgColor} ${textColor} p-4 rounded-lg shadow-lg mb-3 flex items-center animate__animated animate__fadeInRight`;
                toast.innerHTML = `
                    ${icon}
                    <span>${message}</span>
                    <button class="ml-auto text-white/80 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                toastContainer.appendChild(toast);
                
                // Add click event to close button
                toast.querySelector('button').addEventListener('click', () => removeToast(toast));
                
                // Auto remove after 5 seconds
                setTimeout(() => removeToast(toast), 5000);
            }
            
            function removeToast(toast) {
                toast.classList.remove('animate__fadeInRight');
                toast.classList.add('animate__fadeOutRight');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        });
    </script>
</body>
</html>