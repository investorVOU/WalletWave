<?php
session_start();
include_once 'includes/db.php';

// Check if user has a connected wallet
$walletConnected = false;
$walletAddress = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM guest_wallets WHERE session_id = ? AND is_connected = TRUE LIMIT 1");
    $stmt->execute([session_id()]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($wallet) {
        $walletConnected = true;
        $walletAddress = $wallet['address'];
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Get campaign ID from URL
$campaignId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$campaign = null;
$formError = '';
$networkInfo = null;

// Get campaign details if ID is provided
if ($campaignId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT c.*, n.name as network_name, n.short_name as network_short_name, 
                               n.rpc_url, n.chain_id, n.block_explorer_url, n.native_currency_symbol, 
                               n.native_currency_decimals
                               FROM campaigns c
                               LEFT JOIN blockchain_networks n ON c.blockchain_network = n.chain_id
                               WHERE c.id = ? AND c.status = 'approved'");
        $stmt->execute([$campaignId]);
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($campaign) {
            // Calculate days left
            $endDate = new DateTime($campaign['end_date']);
            $now = new DateTime();
            $interval = $now->diff($endDate);
            $campaign['days_left'] = $interval->days;
            
            // Calculate progress percentage
            $campaign['progress_percent'] = 0;
            
            // Get total contributions
            $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM contributions WHERE campaign_id = ? AND status = 'confirmed'");
            $stmt->execute([$campaignId]);
            $totalContributions = $stmt->fetchColumn();
            
            if ($totalContributions) {
                $campaign['current_amount'] = $totalContributions;
                $campaign['progress_percent'] = min(100, round(($totalContributions / $campaign['funding_goal']) * 100));
            } else {
                $campaign['current_amount'] = 0;
            }
            
            // Get network information
            $networkInfo = [
                'chain_id' => $campaign['chain_id'],
                'name' => $campaign['network_name'],
                'short_name' => $campaign['network_short_name'],
                'rpc_url' => $campaign['rpc_url'],
                'block_explorer_url' => $campaign['block_explorer_url'],
                'native_currency_symbol' => $campaign['native_currency_symbol'],
                'native_currency_decimals' => $campaign['native_currency_decimals']
            ];
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $formError = 'Error retrieving campaign information.';
    }
}

// Handle form submission (will be processed by JavaScript/Web3)
$formSubmitted = false;
$formSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $walletConnected && $campaign) {
    $amount = floatval($_POST['amount'] ?? 0);
    $formSubmitted = true;
    
    // Validation only - actual transaction is handled by JavaScript
    if ($amount <= 0) {
        $formError = 'Please enter a valid amount to stake.';
    } elseif ($campaign['staking_enabled'] && $amount < $campaign['min_stake_amount']) {
        $formError = 'Minimum staking amount is ' . $campaign['min_stake_amount'] . ' ' . $campaign['token_symbol'];
    }
}

// Get user's stakes for this campaign
$userStakes = [];
if ($walletConnected && $campaignId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT s.*, c.amount as contribution_amount, c.transaction_hash 
                               FROM stakes s
                               JOIN contributions c ON s.contribution_id = c.id
                               WHERE s.wallet_address = ? AND c.campaign_id = ?
                               ORDER BY s.created_at DESC");
        $stmt->execute([$walletAddress, $campaignId]);
        $userStakes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error fetching stakes: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $campaign ? 'Stake on ' . htmlspecialchars($campaign['title']) : 'Stake on Campaign'; ?> - CryptoFund</title>
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
        <!-- Page Header -->
        <div class="mb-10">
            <h1 class="text-3xl font-bold mb-2 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">
                <?php echo $campaign ? 'Stake on "' . htmlspecialchars($campaign['title']) . '"' : 'Stake on Campaign'; ?>
            </h1>
            <p class="text-gray-400">Support this project by staking your crypto assets and earn rewards.</p>
        </div>
        
        <?php if (!$campaign): ?>
        <!-- Campaign Not Found -->
        <div class="bg-slate-800/80 rounded-xl p-10 border border-red-500/20 shadow-lg text-center max-w-3xl mx-auto animate__animated animate__fadeIn">
            <div class="text-6xl text-red-400 mb-6">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h2 class="text-2xl font-bold mb-3">Campaign Not Found</h2>
            <p class="text-gray-400 mb-8">The campaign you're looking for doesn't exist or has not been approved yet.</p>
            <a href="campaigns.php" class="px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2 mx-auto inline-flex">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>
        <?php elseif (!$walletConnected): ?>
        <!-- Wallet Connection Required -->
        <div class="bg-slate-800/80 rounded-xl p-10 border border-blue-500/20 shadow-lg text-center max-w-3xl mx-auto animate__animated animate__fadeIn">
            <div class="text-6xl text-blue-400 mb-6">
                <i class="fas fa-wallet"></i>
            </div>
            <h2 class="text-2xl font-bold mb-3">Connect Your Wallet First</h2>
            <p class="text-gray-400 mb-8">You need to connect your wallet to stake on this campaign.</p>
            <button id="connectWalletBtn" class="connect-wallet-btn px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2 mx-auto">
                <i class="fas fa-plug"></i> Connect Wallet
            </button>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Campaign Info (Left Column) -->
            <div class="lg:col-span-2">
                <div class="bg-slate-800/50 rounded-xl overflow-hidden shadow-lg border border-blue-500/20">
                    <img src="<?php echo htmlspecialchars($campaign['thumbnail_url'] ?? 'https://images.unsplash.com/photo-1639988525250-a93576693f65'); ?>" 
                         alt="<?php echo htmlspecialchars($campaign['title']); ?>" 
                         class="w-full h-64 object-cover">
                    
                    <div class="p-8">
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <span class="bg-blue-600/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold">
                                <?php echo htmlspecialchars($campaign['category'] ?? 'General'); ?>
                            </span>
                            
                            <span class="bg-purple-600/20 text-purple-400 px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1">
                                <i class="fas fa-network-wired text-xs"></i>
                                <?php echo htmlspecialchars($campaign['network_name'] ?? 'Unknown Network'); ?>
                            </span>
                            
                            <?php if ($campaign['staking_enabled']): ?>
                            <span class="bg-green-600/20 text-green-400 px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1">
                                <i class="fas fa-percentage text-xs"></i>
                                <?php echo htmlspecialchars($campaign['staking_apy']); ?>% APY
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <h2 class="text-2xl font-bold mb-3"><?php echo htmlspecialchars($campaign['title']); ?></h2>
                        
                        <div class="mb-6">
                            <p class="text-gray-300 mb-4">
                                <?php echo nl2br(htmlspecialchars($campaign['description'])); ?>
                            </p>
                        </div>
                        
                        <div class="mb-6">
                            <div class="h-2 w-full bg-gray-700 rounded-full">
                                <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: <?php echo $campaign['progress_percent']; ?>%"></div>
                            </div>
                            <div class="flex justify-between text-sm mt-2">
                                <span class="text-blue-400 font-semibold">
                                    <?php echo $campaign['current_amount']; ?> 
                                    <?php echo htmlspecialchars($campaign['token_symbol']); ?> raised
                                </span>
                                <span class="text-gray-400">
                                    of <?php echo htmlspecialchars($campaign['funding_goal']); ?> 
                                    <?php echo htmlspecialchars($campaign['token_symbol']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-slate-900/80 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-400">Days Left</p>
                                <p class="text-xl font-bold text-white"><?php echo $campaign['days_left']; ?></p>
                            </div>
                            <div class="bg-slate-900/80 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-400">Token</p>
                                <p class="text-xl font-bold text-white"><?php echo htmlspecialchars($campaign['token_symbol']); ?></p>
                            </div>
                            <?php if ($campaign['staking_enabled']): ?>
                            <div class="bg-slate-900/80 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-400">Min Stake</p>
                                <p class="text-xl font-bold text-white"><?php echo htmlspecialchars($campaign['min_stake_amount']); ?></p>
                            </div>
                            <div class="bg-slate-900/80 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-400">Lock Period</p>
                                <p class="text-xl font-bold text-white"><?php echo htmlspecialchars($campaign['staking_duration_days']); ?> days</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($campaign['contract_address'])): ?>
                        <div class="mb-6 bg-slate-900/50 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-400">Contract Address:</span>
                                <a href="<?php echo htmlspecialchars($campaign['block_explorer_url']); ?>/address/<?php echo htmlspecialchars($campaign['contract_address']); ?>" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="text-blue-400 hover:text-blue-300 text-sm">
                                    <?php echo substr(htmlspecialchars($campaign['contract_address']), 0, 8) . '...' . substr(htmlspecialchars($campaign['contract_address']), -6); ?>
                                    <i class="fas fa-external-link-alt text-xs ml-1"></i>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- User's Active Stakes -->
                <?php if (!empty($userStakes)): ?>
                <div class="mt-8 bg-slate-800/50 rounded-xl p-6 shadow-lg border border-green-500/20">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-gem text-green-400"></i> Your Active Stakes
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="text-left py-2 px-4 text-gray-400 font-medium">Amount</th>
                                    <th class="text-left py-2 px-4 text-gray-400 font-medium">APY</th>
                                    <th class="text-left py-2 px-4 text-gray-400 font-medium">Start Date</th>
                                    <th class="text-left py-2 px-4 text-gray-400 font-medium">End Date</th>
                                    <th class="text-left py-2 px-4 text-gray-400 font-medium">Status</th>
                                    <th class="text-left py-2 px-4 text-gray-400 font-medium">Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userStakes as $stake): ?>
                                <tr class="border-b border-gray-800 hover:bg-slate-700/20">
                                    <td class="py-3 px-4 font-medium">
                                        <?php echo htmlspecialchars($stake['amount']); ?> 
                                        <?php echo htmlspecialchars($campaign['token_symbol']); ?>
                                    </td>
                                    <td class="py-3 px-4 text-green-400">
                                        <?php echo htmlspecialchars($stake['apy']); ?>%
                                    </td>
                                    <td class="py-3 px-4 text-gray-300">
                                        <?php echo date('M j, Y', strtotime($stake['start_date'])); ?>
                                    </td>
                                    <td class="py-3 px-4 text-gray-300">
                                        <?php echo date('M j, Y', strtotime($stake['end_date'])); ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                        <?php echo $stake['status'] === 'active' ? 'bg-green-600/20 text-green-400' : 
                                                  ($stake['status'] === 'ended' ? 'bg-orange-600/20 text-orange-400' : 
                                                   'bg-blue-600/20 text-blue-400'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($stake['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($stake['transaction_hash'])): ?>
                                        <a href="<?php echo htmlspecialchars($campaign['block_explorer_url']); ?>/tx/<?php echo htmlspecialchars($stake['transaction_hash']); ?>" 
                                           target="_blank" rel="noopener noreferrer"
                                           class="text-blue-400 hover:text-blue-300 text-sm">
                                            View <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-gray-500">N/A</span>
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
            
            <!-- Staking Form (Right Column) -->
            <div class="lg:col-span-1">
                <div class="bg-slate-800/50 rounded-xl p-6 shadow-lg border border-blue-500/20 sticky top-8">
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-hand-holding-usd text-blue-400 mr-2"></i> Stake on this Campaign
                    </h3>
                    
                    <?php if ($formError): ?>
                    <div class="bg-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-4">
                        <p><i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($formError); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-4 bg-slate-900/50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Connected Wallet:</span>
                            <span class="font-mono text-blue-400 text-sm"><?php echo substr($walletAddress, 0, 6) . '...' . substr($walletAddress, -4); ?></span>
                        </div>
                    </div>
                    
                    <form id="stakeForm" class="space-y-4">
                        <input type="hidden" id="campaignId" value="<?php echo $campaignId; ?>">
                        <input type="hidden" id="chainId" value="<?php echo $networkInfo ? $networkInfo['chain_id'] : ''; ?>">
                        <input type="hidden" id="tokenSymbol" value="<?php echo htmlspecialchars($campaign['token_symbol']); ?>">
                        <input type="hidden" id="contractAddress" value="<?php echo htmlspecialchars($campaign['contract_address'] ?? ''); ?>">
                        
                        <?php if ($campaign['staking_enabled']): ?>
                        <div>
                            <label class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-300">Amount to Stake</span>
                                <span class="text-xs text-blue-400">Min: <?php echo htmlspecialchars($campaign['min_stake_amount']); ?> <?php echo htmlspecialchars($campaign['token_symbol']); ?></span>
                            </label>
                            <div class="relative">
                                <input type="number" id="stakeAmount" name="amount" min="<?php echo htmlspecialchars($campaign['min_stake_amount']); ?>" step="0.001" required
                                    class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 pr-16 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                                    placeholder="0.00">
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <?php echo htmlspecialchars($campaign['token_symbol']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-300">Staking Term</span>
                                <span class="text-blue-400">Choose your staking period</span>
                            </div>
                            <div class="bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3">
                                <div class="mb-3">
                                    <label for="stakingPeriod" class="block text-sm font-medium text-gray-300 mb-1">Lock Period (days):</label>
                                    <input type="range" id="stakingPeriod" name="stakingPeriod" 
                                        min="30" max="365" value="<?php echo htmlspecialchars($campaign['staking_duration_days']); ?>" 
                                        class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                                        <span>30 days</span>
                                        <span id="stakingPeriodValue"><?php echo htmlspecialchars($campaign['staking_duration_days']); ?> days</span>
                                        <span>365 days</span>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span>APY:</span>
                                    <span class="text-green-400"><?php echo htmlspecialchars($campaign['staking_apy']); ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="text-sm font-medium text-gray-300 mb-1">Estimated Rewards</div>
                            <div class="bg-green-900/20 border border-green-500/20 rounded-lg px-4 py-3">
                                <div class="flex justify-between">
                                    <span>After <?php echo htmlspecialchars($campaign['staking_duration_days']); ?> days:</span>
                                    <span id="estimatedReward" class="text-green-400 font-semibold">
                                        0 <?php echo htmlspecialchars($campaign['token_symbol']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-900/10 border border-blue-500/20 rounded-lg p-4 text-sm text-gray-400">
                            <i class="fas fa-info-circle text-blue-400 mr-1"></i> By staking, your tokens will be locked for the specified period. You'll earn rewards at the end of the staking term.
                        </div>
                        
                        <button type="button" id="stakeButton" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-layer-group"></i> Stake Now
                        </button>
                        <?php else: ?>
                        <div>
                            <label class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-300">Amount to Fund</span>
                            </label>
                            <div class="relative">
                                <input type="number" id="fundAmount" name="amount" min="0.001" step="0.001" required
                                    class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 pr-16 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                                    placeholder="0.00">
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <?php echo htmlspecialchars($campaign['token_symbol']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="bg-blue-900/10 border border-blue-500/20 rounded-lg p-4 text-sm text-gray-400">
                            <i class="fas fa-info-circle text-blue-400 mr-1"></i> This campaign doesn't offer staking rewards, but your contribution will help fund the project directly.
                        </div>
                        
                        <button type="button" id="fundButton" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-hand-holding-usd"></i> Fund Campaign
                        </button>
                        <?php endif; ?>
                    </form>
                    
                    <div class="mt-4 flex justify-center">
                        <a href="campaigns.php" class="text-gray-400 hover:text-white text-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Campaigns
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Wallet Connection Modal -->
    <div id="walletConnectionStatus" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-75 backdrop-blur-sm" id="walletModalOverlay"></div>
        <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 border border-blue-500/30">
            <div id="connectionLoading" class="text-center py-6">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <h3 class="text-xl font-semibold mb-2">Connecting Wallet</h3>
                <p class="text-gray-400">Please confirm the connection in your wallet...</p>
            </div>
            
            <div id="connectionSuccess" class="text-center py-6 hidden">
                <div class="bg-green-500/20 text-green-400 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Wallet Connected!</h3>
                <p class="text-gray-400 mb-4">Your wallet has been successfully connected.</p>
                <div class="bg-slate-800 p-4 rounded-lg flex items-center justify-between mb-6">
                    <span id="connectedAddress" class="text-blue-400 font-mono">0x1234...5678</span>
                    <button id="copyAddressBtn" class="text-gray-400 hover:text-white">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <button id="continueBtn" class="w-full py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300">
                    Continue
                </button>
            </div>
            
            <div id="connectionError" class="text-center py-6 hidden">
                <div class="bg-red-500/20 text-red-400 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Connection Failed</h3>
                <p id="errorMessage" class="text-gray-400 mb-6">Unable to connect to your wallet. Please try again.</p>
                <button id="retryBtn" class="w-full py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300">
                    Try Again
                </button>
                <button id="closeErrorBtn" class="w-full py-3 mt-3 rounded-lg bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-800 font-semibold transition duration-300">
                    Cancel
                </button>
            </div>
            
            <button id="closeModalBtn" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <!-- Transaction Processing Modal -->
    <div id="transactionModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-75 backdrop-blur-sm"></div>
        <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 border border-blue-500/30">
            <div id="txProcessing" class="text-center py-6">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <h3 class="text-xl font-semibold mb-2">Processing Transaction</h3>
                <p class="text-gray-400">Please confirm the transaction in your wallet and wait for it to be processed...</p>
            </div>
            
            <div id="txSuccess" class="text-center py-6 hidden">
                <div class="bg-green-500/20 text-green-400 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Transaction Successful!</h3>
                <p class="text-gray-400 mb-4">Your transaction has been confirmed on the blockchain.</p>
                <div id="txHashContainer" class="bg-slate-800 p-4 rounded-lg flex items-center justify-between mb-6">
                    <span id="txHash" class="text-blue-400 font-mono text-sm">0x1234...5678</span>
                    <button id="copyTxBtn" class="text-gray-400 hover:text-white">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <a id="viewTxLink" href="#" target="_blank" rel="noopener noreferrer" class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2 mb-4">
                    <i class="fas fa-external-link-alt"></i> View on Explorer
                </a>
                <button id="closeTxBtn" class="w-full py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-semibold shadow-lg transition duration-300">
                    Close
                </button>
            </div>
            
            <div id="txError" class="text-center py-6 hidden">
                <div class="bg-red-500/20 text-red-400 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Transaction Failed</h3>
                <p id="txErrorMessage" class="text-gray-400 mb-6">There was an error processing your transaction. Please try again.</p>
                <button id="retryTxBtn" class="w-full py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300">
                    Try Again
                </button>
                <button id="closeTxErrorBtn" class="w-full py-3 mt-3 rounded-lg bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-800 font-semibold transition duration-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Network Switch Modal -->
    <div id="networkSwitchModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-75 backdrop-blur-sm"></div>
        <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl relative z-10 w-full max-w-md mx-4 border border-blue-500/30">
            <div class="text-center py-6">
                <div class="bg-yellow-500/20 text-yellow-400 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exchange-alt text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Network Switch Required</h3>
                <p class="text-gray-400 mb-6">This campaign requires the <span id="requiredNetwork" class="text-yellow-400 font-semibold">Ethereum</span> network. Please switch networks in your wallet to continue.</p>
                <button id="switchNetworkBtn" class="w-full py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300">
                    Switch Network
                </button>
                <button id="closeNetworkModalBtn" class="w-full py-3 mt-3 rounded-lg bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-800 font-semibold transition duration-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    
    <?php include_once 'includes/footer.php'; ?>
    
    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50"></div>
    
    <!-- Web3 Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.4/dist/web3.min.js"></script>
    <script src="https://cdn.ethers.io/lib/ethers-5.6.umd.min.js"></script>
    
    <!-- Custom JS files -->
    <script src="js/wallet.js"></script>
    <script src="js/contract.js"></script>
    <script src="js/app.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initWalletConnection === 'function') {
                initWalletConnection();
            }
            
            // Handle staking period slider
            const stakingPeriodSlider = document.getElementById('stakingPeriod');
            const stakingPeriodValue = document.getElementById('stakingPeriodValue');
            
            if (stakingPeriodSlider && stakingPeriodValue) {
                stakingPeriodSlider.addEventListener('input', function() {
                    stakingPeriodValue.textContent = this.value + ' days';
                    calculateRewards();
                });
            }
            
            // Calculate estimated rewards when amount changes
            const stakeAmountInput = document.getElementById('stakeAmount');
            if (stakeAmountInput) {
                stakeAmountInput.addEventListener('input', calculateRewards);
            }
            
            function calculateRewards() {
                const amount = parseFloat(stakeAmountInput.value) || 0;
                const apy = <?php echo $campaign ? $campaign['staking_apy'] : 0; ?>;
                // Get staking period from the slider
                const days = parseInt(stakingPeriodSlider.value) || <?php echo $campaign ? $campaign['staking_duration_days'] : 30; ?>;
                const tokenSymbol = "<?php echo $campaign ? htmlspecialchars($campaign['token_symbol']) : ''; ?>";
                
                // Update the days display in the Estimated Rewards section
                const daysDisplay = document.querySelector('#estimatedReward').parentNode.previousElementSibling;
                if (daysDisplay) {
                    daysDisplay.textContent = `After ${days} days:`;
                }
                
                if (amount > 0 && apy > 0 && days > 0) {
                    // Calculate daily interest rate
                    const dailyRate = apy / 365 / 100;
                    // Calculate reward (simple interest)
                    const reward = amount * dailyRate * days;
                    // Display result with 6 decimal places
                    document.getElementById('estimatedReward').textContent = 
                        reward.toFixed(6) + ' ' + tokenSymbol;
                } else {
                    document.getElementById('estimatedReward').textContent = 
                        '0 ' + tokenSymbol;
                }
            }
            
            // Staking Button Event
            const stakeButton = document.getElementById('stakeButton');
            if (stakeButton) {
                stakeButton.addEventListener('click', initiateStaking);
            }
            
            // Fund Button Event
            const fundButton = document.getElementById('fundButton');
            if (fundButton) {
                fundButton.addEventListener('click', initiateFunding);
            }
            
            // Network handling
            const networkInfo = <?php echo $networkInfo ? json_encode($networkInfo) : 'null'; ?>;
            
            async function initiateStaking() {
                // Show processing modal
                document.getElementById('transactionModal').classList.remove('hidden');
                document.getElementById('txProcessing').classList.remove('hidden');
                document.getElementById('txSuccess').classList.add('hidden');
                document.getElementById('txError').classList.add('hidden');
                
                try {
                    // Check if wallet is connected
                    if (!selectedAccount) {
                        throw new Error('Wallet not connected');
                    }
                    
                    // Check if on the correct network
                    if (networkInfo && currentChainId !== networkInfo.chain_id) {
                        // Show network switch modal
                        document.getElementById('requiredNetwork').textContent = networkInfo.name;
                        document.getElementById('transactionModal').classList.add('hidden');
                        document.getElementById('networkSwitchModal').classList.remove('hidden');
                        return;
                    }
                    
                    // Get form values
                    const amount = parseFloat(document.getElementById('stakeAmount').value);
                    const stakingPeriod = parseInt(document.getElementById('stakingPeriod').value);
                    const campaignId = document.getElementById('campaignId').value;
                    
                    if (isNaN(amount) || amount <= 0) {
                        throw new Error('Please enter a valid amount');
                    }
                    
                    if (isNaN(stakingPeriod) || stakingPeriod < 30 || stakingPeriod > 365) {
                        throw new Error('Staking period must be between 30 and 365 days');
                    }
                    
                    const minAmount = <?php echo $campaign ? $campaign['min_stake_amount'] : 0; ?>;
                    if (amount < minAmount) {
                        throw new Error(`Minimum staking amount is ${minAmount}`);
                    }
                    
                    // Load the contract.js script if not already loaded
                    if (typeof contributeWithStaking !== 'function') {
                        // Dynamically load the contract.js script
                        await new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            script.src = '/js/contract.js';
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                        
                        // Wait for ethers.js to load if needed
                        if (typeof ethers === 'undefined') {
                            await new Promise((resolve, reject) => {
                                const script = document.createElement('script');
                                script.src = 'https://cdn.ethers.io/lib/ethers-5.6.umd.min.js';
                                script.onload = resolve;
                                script.onerror = reject;
                                document.head.appendChild(script);
                            });
                        }
                        
                        // Wait a bit for the script to initialize
                        await new Promise(resolve => setTimeout(resolve, 500));
                    }
                    
                    // Execute the contract function if available
                    let txHash;
                    if (typeof contributeWithStaking === 'function') {
                        console.log('Using smart contract to stake', { campaignId, amount, stakingPeriod });
                        
                        try {
                            // Call the contract function
                            const result = await contributeWithStaking(campaignId, amount, stakingPeriod);
                            txHash = result.transactionHash;
                        } catch (error) {
                            console.error('Contract call failed:', error);
                            throw new Error(error.message || 'Failed to execute transaction');
                        }
                    } else {
                        console.log('Smart contract integration not available, using simulated transaction');
                        // Simulate blockchain delay
                        await new Promise(resolve => setTimeout(resolve, 2000));
                        // Create fake transaction hash for demo purposes
                        txHash = '0x' + Array(64).fill(0).map(() => Math.floor(Math.random() * 16).toString(16)).join('');
                    }
                    
                    // Save staking details to the database
                    const response = await fetch('/api/contribute.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            campaign_id: campaignId,
                            amount: amount,
                            wallet_address: selectedAccount,
                            transaction_hash: txHash,
                            staking: true,
                            staking_period: stakingPeriod
                        }),
                    });
                    
                    const data = await response.json();
                    console.log("Staking response:", data);
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to record stake');
                    }
                    
                    // Show success
                    document.getElementById('txProcessing').classList.add('hidden');
                    document.getElementById('txSuccess').classList.remove('hidden');
                    
                    // Update UI with transaction hash
                    document.getElementById('txHash').textContent = txHash.substring(0, 10) + '...' + txHash.substring(txHash.length - 8);
                    if (networkInfo && networkInfo.block_explorer_url) {
                        document.getElementById('viewTxLink').href = `${networkInfo.block_explorer_url}/tx/${txHash}`;
                    }
                    
                } catch (error) {
                    console.error('Staking error:', error);
                    document.getElementById('txProcessing').classList.add('hidden');
                    document.getElementById('txError').classList.remove('hidden');
                    document.getElementById('txErrorMessage').textContent = error.message || 'Transaction failed';
                }
            }
            
            async function initiateFunding() {
                // Similar to staking function but for direct funding
                document.getElementById('transactionModal').classList.remove('hidden');
                document.getElementById('txProcessing').classList.remove('hidden');
                document.getElementById('txSuccess').classList.add('hidden');
                document.getElementById('txError').classList.add('hidden');
                
                try {
                    if (!selectedAccount) {
                        throw new Error('Wallet not connected');
                    }
                    
                    if (networkInfo && currentChainId !== networkInfo.chain_id) {
                        document.getElementById('requiredNetwork').textContent = networkInfo.name;
                        document.getElementById('transactionModal').classList.add('hidden');
                        document.getElementById('networkSwitchModal').classList.remove('hidden');
                        return;
                    }
                    
                    const amount = parseFloat(document.getElementById('fundAmount').value);
                    const campaignId = document.getElementById('campaignId').value;
                    
                    if (isNaN(amount) || amount <= 0) {
                        throw new Error('Please enter a valid amount');
                    }
                    
                    if (!campaignId) {
                        throw new Error('Campaign ID is missing');
                    }
                    
                    // Load the contract.js script if not already loaded
                    if (typeof contribute !== 'function') {
                        // Dynamically load the contract.js script
                        await new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            script.src = '/js/contract.js';
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                        
                        // Wait for ethers.js to load if needed
                        if (typeof ethers === 'undefined') {
                            await new Promise((resolve, reject) => {
                                const script = document.createElement('script');
                                script.src = 'https://cdn.ethers.io/lib/ethers-5.6.umd.min.js';
                                script.onload = resolve;
                                script.onerror = reject;
                                document.head.appendChild(script);
                            });
                        }
                        
                        // Wait a bit for the script to initialize
                        await new Promise(resolve => setTimeout(resolve, 500));
                    }
                    
                    // Execute the contract function if available
                    let txHash;
                    if (typeof contribute === 'function') {
                        console.log('Using smart contract to contribute', { campaignId, amount });
                        
                        try {
                            // Call the contract function
                            const result = await contribute(campaignId, amount);
                            txHash = result.transactionHash;
                        } catch (error) {
                            console.error('Contract call failed:', error);
                            throw new Error(error.message || 'Failed to execute transaction');
                        }
                    } else {
                        console.log('Smart contract integration not available, using simulated transaction');
                        // Simulate blockchain delay
                        await new Promise(resolve => setTimeout(resolve, 2000));
                        // Create fake transaction hash for demo purposes
                        txHash = '0x' + Array(64).fill(0).map(() => Math.floor(Math.random() * 16).toString(16)).join('');
                    }
                    
                    // Save contribution to database
                    const response = await fetch('/api/contribute.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            campaign_id: campaignId,
                            amount: amount,
                            wallet_address: selectedAccount,
                            transaction_hash: txHash,
                            staking: false // Direct funding, not staking
                        }),
                    });
                    
                    const data = await response.json();
                    console.log("Contribution response:", data);
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to record contribution');
                    }
                    
                    // Update the UI with transaction info
                    document.getElementById('txProcessing').classList.add('hidden');
                    document.getElementById('txSuccess').classList.remove('hidden');
                    document.getElementById('txHash').textContent = txHash.substring(0, 10) + '...' + txHash.substring(txHash.length - 8);
                    
                    if (networkInfo && networkInfo.block_explorer_url) {
                        document.getElementById('viewTxLink').href = `${networkInfo.block_explorer_url}/tx/${txHash}`;
                    }
                    
                } catch (error) {
                    console.error('Funding error:', error);
                    document.getElementById('txProcessing').classList.add('hidden');
                    document.getElementById('txError').classList.remove('hidden');
                    document.getElementById('txErrorMessage').textContent = error.message || 'Transaction failed';
                }
            }
            
            // Setup Modal Event Handlers
            document.getElementById('closeTxBtn').addEventListener('click', function() {
                document.getElementById('transactionModal').classList.add('hidden');
                window.location.reload(); // Reload to show updated state
            });
            
            document.getElementById('closeTxErrorBtn').addEventListener('click', function() {
                document.getElementById('transactionModal').classList.add('hidden');
            });
            
            document.getElementById('closeNetworkModalBtn').addEventListener('click', function() {
                document.getElementById('networkSwitchModal').classList.add('hidden');
            });
            
            document.getElementById('switchNetworkBtn').addEventListener('click', async function() {
                if (!networkInfo) return;
                
                try {
                    // Request network switch
                    await window.ethereum.request({
                        method: 'wallet_switchEthereumChain',
                        params: [{ chainId: '0x' + networkInfo.chain_id.toString(16) }],
                    });
                    
                    document.getElementById('networkSwitchModal').classList.add('hidden');
                    // Give time for network to switch
                    setTimeout(() => {
                        if (stakeButton) stakeButton.click();
                        if (fundButton) fundButton.click();
                    }, 1000);
                    
                } catch (error) {
                    console.error('Error switching network:', error);
                    showToast('Failed to switch network. Please try manually in your wallet.', 'error');
                    document.getElementById('networkSwitchModal').classList.add('hidden');
                }
            });
            
            // Reload page after successful wallet connection
            const continueBtn = document.getElementById('continueBtn');
            if (continueBtn) {
                continueBtn.addEventListener('click', function() {
                    window.location.reload();
                });
            }
            
            // Copy transaction hash
            document.getElementById('copyTxBtn').addEventListener('click', function() {
                const hash = document.getElementById('txHash').textContent;
                navigator.clipboard.writeText(hash)
                    .then(() => showToast('Transaction hash copied!', 'success'))
                    .catch(err => console.error('Could not copy text: ', err));
            });
        });
    </script>
</body>
</html>