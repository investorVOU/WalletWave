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

// Get blockchain networks
$networks = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM blockchain_networks WHERE is_enabled = TRUE ORDER BY is_testnet ASC, name ASC");
    $stmt->execute();
    $networks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching networks: " . $e->getMessage());
}

// Handle form submission
$formSubmitted = false;
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $walletConnected) {
    // Validate form data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $fundingGoal = floatval($_POST['funding_goal'] ?? 0);
    $tokenSymbol = trim($_POST['token_symbol'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $blockchainNetwork = intval($_POST['blockchain_network'] ?? 0);
    $thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');
    $contractAddress = trim($_POST['contract_address'] ?? '');
    
    // Handle staking options
    $stakingEnabled = isset($_POST['staking_enabled']) ? 1 : 0;
    $stakingApy = floatval($_POST['staking_apy'] ?? 0);
    $minStakeAmount = floatval($_POST['min_stake_amount'] ?? 0);
    $stakingDurationDays = intval($_POST['staking_duration_days'] ?? 0);
    
    $formSubmitted = true;
    
    // Validate required fields
    if (empty($title) || empty($description) || $fundingGoal <= 0 || empty($tokenSymbol) || 
        empty($category) || empty($startDate) || empty($endDate) || $blockchainNetwork <= 0) {
        $formError = 'Please fill in all required fields';
    } else {
        try {
            // Get user ID from wallet
            $stmt = $pdo->prepare("SELECT id FROM guest_wallets WHERE address = ? LIMIT 1");
            $stmt->execute([$walletAddress]);
            $userId = $stmt->fetchColumn();
            
            if (!$userId) {
                throw new Exception("Wallet not found in database");
            }
            
            // Insert campaign - default status is 'pending'
            $stmt = $pdo->prepare("INSERT INTO campaigns (
                user_id, title, description, funding_goal, token_symbol, category, 
                start_date, end_date, thumbnail_url, contract_address, status, 
                blockchain_network, staking_enabled, staking_apy, min_stake_amount, staking_duration_days,
                created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, 'pending', 
                ?, ?, ?, ?, ?,
                CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )");
            
            $stmt->execute([
                $userId, $title, $description, $fundingGoal, $tokenSymbol, $category,
                $startDate, $endDate, $thumbnailUrl, $contractAddress,
                $blockchainNetwork, $stakingEnabled, $stakingApy, $minStakeAmount, $stakingDurationDays
            ]);
            
            $formSuccess = true;
        } catch (Exception $e) {
            error_log("Error creating campaign: " . $e->getMessage());
            $formError = 'An error occurred while creating your campaign. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Campaign - CryptoFund</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Flatpickr for date inputs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="bg-gradient-to-r from-slate-900 to-slate-800 text-white min-h-screen">
    <?php include_once 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-10">
            <h1 class="text-3xl font-bold mb-2 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">
                Create a Campaign
            </h1>
            <p class="text-gray-400 max-w-2xl">Launch your project on the blockchain and gather support from the crypto community. All campaigns undergo a brief review before being published.</p>
        </div>
        
        <?php if (!$walletConnected): ?>
        <!-- Wallet Connection Required -->
        <div class="bg-slate-800/80 rounded-xl p-10 border border-blue-500/20 shadow-lg text-center max-w-3xl mx-auto animate__animated animate__fadeIn">
            <div class="text-6xl text-blue-400 mb-6">
                <i class="fas fa-wallet"></i>
            </div>
            <h2 class="text-2xl font-bold mb-3">Connect Your Wallet First</h2>
            <p class="text-gray-400 mb-8">You need to connect your wallet to create a campaign. This links your campaign to your blockchain identity.</p>
            <button id="connectWalletBtn" class="connect-wallet-btn px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2 mx-auto">
                <i class="fas fa-plug"></i> Connect Wallet
            </button>
        </div>
        <?php elseif ($formSubmitted && $formSuccess): ?>
        <!-- Success Message -->
        <div class="bg-slate-800/80 rounded-xl p-10 border border-green-500/20 shadow-lg text-center max-w-3xl mx-auto animate__animated animate__fadeIn">
            <div class="text-6xl text-green-400 mb-6">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-2xl font-bold mb-3">Campaign Created Successfully!</h2>
            <p class="text-gray-400 mb-8">Your campaign has been submitted and is pending review. We'll notify you once it's approved and live on the platform.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="campaigns.php" class="px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300">
                    <i class="fas fa-list-ul mr-2"></i> View All Campaigns
                </a>
                <a href="create-campaign.php" class="px-8 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-semibold shadow-lg transition duration-300">
                    <i class="fas fa-plus mr-2"></i> Create Another Campaign
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Campaign Creation Form -->
        <form method="POST" action="create-campaign.php" class="max-w-4xl mx-auto bg-slate-800/50 rounded-xl p-8 shadow-lg border border-blue-500/20">
            <?php if ($formSubmitted && !$formSuccess): ?>
                <div class="bg-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6">
                    <p class="font-medium"><i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($formError); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="flex items-center gap-2 mb-8 border-b border-slate-700 pb-4">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Connected wallet</p>
                    <p class="font-mono font-medium text-blue-400"><?php echo htmlspecialchars($walletAddress); ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Campaign Title -->
                <div class="col-span-full">
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Campaign Title <span class="text-blue-400">*</span></label>
                    <input type="text" id="title" name="title" required 
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors" 
                        placeholder="Enter a catchy title for your campaign">
                </div>
                
                <!-- Description -->
                <div class="col-span-full">
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Description <span class="text-blue-400">*</span></label>
                    <textarea id="description" name="description" rows="5" required
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                        placeholder="Describe your project, goals, and how funds will be used..."></textarea>
                </div>
                
                <!-- Funding Goal -->
                <div>
                    <label for="funding_goal" class="block text-sm font-medium text-gray-300 mb-1">Funding Goal <span class="text-blue-400">*</span></label>
                    <input type="number" id="funding_goal" name="funding_goal" required min="0.01" step="0.01"
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                        placeholder="Amount needed">
                </div>
                
                <!-- Token Symbol -->
                <div>
                    <label for="token_symbol" class="block text-sm font-medium text-gray-300 mb-1">Token Symbol <span class="text-blue-400">*</span></label>
                    <input type="text" id="token_symbol" name="token_symbol" required
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                        placeholder="ETH, USDC, BNB, etc.">
                </div>
                
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-300 mb-1">Category <span class="text-blue-400">*</span></label>
                    <select id="category" name="category" required
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors">
                        <option value="">Select a category</option>
                        <option value="NFT">NFT Project</option>
                        <option value="DeFi">DeFi Protocol</option>
                        <option value="GameFi">GameFi</option>
                        <option value="DAO">DAO</option>
                        <option value="Metaverse">Metaverse</option>
                        <option value="Social">Social Impact</option>
                        <option value="Infrastructure">Infrastructure</option>
                        <option value="Web3">Web3</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <!-- Blockchain Network -->
                <div>
                    <label for="blockchain_network" class="block text-sm font-medium text-gray-300 mb-1">Blockchain Network <span class="text-blue-400">*</span></label>
                    <select id="blockchain_network" name="blockchain_network" required
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors">
                        <option value="">Select network</option>
                        <?php foreach ($networks as $network): ?>
                            <option value="<?php echo $network['chain_id']; ?>" data-testnet="<?php echo $network['is_testnet']; ?>">
                                <?php echo htmlspecialchars($network['name']); ?>
                                <?php if ($network['is_testnet']): ?> (Testnet)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 italic">Select the blockchain network where your campaign will be deployed</p>
                </div>
                
                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-300 mb-1">Start Date <span class="text-blue-400">*</span></label>
                    <input type="text" id="start_date" name="start_date" required
                        class="datepicker w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                        placeholder="YYYY-MM-DD">
                </div>
                
                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-300 mb-1">End Date <span class="text-blue-400">*</span></label>
                    <input type="text" id="end_date" name="end_date" required
                        class="datepicker w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                        placeholder="YYYY-MM-DD">
                </div>
                
                <!-- Thumbnail URL -->
                <div class="col-span-full">
                    <label for="thumbnail_url" class="block text-sm font-medium text-gray-300 mb-1">Thumbnail Image URL</label>
                    <input type="url" id="thumbnail_url" name="thumbnail_url"
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                        placeholder="https://example.com/image.jpg">
                    <p class="mt-1 text-xs text-gray-500 italic">Leave empty to use a default image</p>
                </div>
                
                <!-- Contract Address (optional) -->
                <div class="col-span-full">
                    <label for="contract_address" class="block text-sm font-medium text-gray-300 mb-1">Smart Contract Address (optional)</label>
                    <input type="text" id="contract_address" name="contract_address"
                        class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                        placeholder="0x...">
                    <p class="mt-1 text-xs text-gray-500 italic">If your project already has a deployed contract</p>
                </div>
            </div>
            
            <!-- Staking Options -->
            <div class="bg-blue-900/10 border border-blue-500/20 rounded-lg p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-blue-400">Staking Options</h3>
                    <div class="relative inline-block w-10 mr-2 align-middle select-none">
                        <input type="checkbox" id="staking_enabled" name="staking_enabled" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                        <label for="staking_enabled" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                    </div>
                </div>
                
                <p class="text-sm text-gray-400 mb-4">Enable staking functionality to allow backers to stake tokens and earn rewards for supporting your campaign.</p>
                
                <div id="stakingOptions" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 opacity-50">
                    <!-- APY -->
                    <div>
                        <label for="staking_apy" class="block text-sm font-medium text-gray-300 mb-1">APY (%)</label>
                        <input type="number" id="staking_apy" name="staking_apy" min="0" max="100" step="0.01"
                            class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                            placeholder="Staking APY" value="5.00">
                    </div>
                    
                    <!-- Min Stake Amount -->
                    <div>
                        <label for="min_stake_amount" class="block text-sm font-medium text-gray-300 mb-1">Min Stake Amount</label>
                        <input type="number" id="min_stake_amount" name="min_stake_amount" min="0" step="0.000001"
                            class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                            placeholder="Minimum stake" value="0.00005">
                    </div>
                    
                    <!-- Staking Duration -->
                    <div>
                        <label for="staking_duration_days" class="block text-sm font-medium text-gray-300 mb-1">Lock Period (days)</label>
                        <input type="number" id="staking_duration_days" name="staking_duration_days" min="0" step="1"
                            class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                            placeholder="Lock period" value="30">
                    </div>
                </div>
            </div>
            
            <!-- Disclaimer -->
            <div class="bg-slate-900/50 rounded-lg p-4 mb-8 text-sm text-gray-400">
                <p><i class="fas fa-info-circle text-blue-400 mr-2"></i> By submitting this form, you agree to our terms and conditions. All campaigns undergo a brief review to ensure they meet our community guidelines.</p>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center gap-2">
                    <i class="fas fa-rocket"></i> Submit Campaign
                </button>
            </div>
        </form>
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
    
    <?php include_once 'includes/footer.php'; ?>
    
    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50"></div>
    
    <!-- Web3 Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.4/dist/web3.min.js"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- Custom JS files -->
    <script src="js/wallet.js"></script>
    <script src="js/app.js"></script>
    
    <script>
        // Initialize date pickers
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            minDate: "today",
            altInput: true,
            altFormat: "F j, Y",
        });
        
        // Handle staking toggle
        const stakingEnabled = document.getElementById('staking_enabled');
        const stakingOptions = document.getElementById('stakingOptions');
        
        if (stakingEnabled && stakingOptions) {
            stakingEnabled.addEventListener('change', function() {
                if (this.checked) {
                    stakingOptions.classList.remove('opacity-50');
                } else {
                    stakingOptions.classList.add('opacity-50');
                }
            });
        }
        
        // Initialize wallet connection
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initWalletConnection === 'function') {
                initWalletConnection();
            }
            
            // Reload page after successful wallet connection
            const continueBtn = document.getElementById('continueBtn');
            if (continueBtn) {
                continueBtn.addEventListener('click', function() {
                    window.location.reload();
                });
            }
        });
    </script>
    
    <style>
        /* Custom toggle switch styles */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #3B82F6;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #3B82F6;
        }
    </style>
</body>
</html>