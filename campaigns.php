<?php
session_start();
include_once 'includes/db.php';

// Fetch approved campaigns
try {
    $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE status = 'approved' ORDER BY created_at DESC");
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process campaign data for display
    foreach ($campaigns as &$campaign) {
        // Calculate days left based on end_date
        $endDate = new DateTime($campaign['end_date']);
        $now = new DateTime();
        $interval = $now->diff($endDate);
        $campaign['days_left'] = $interval->days;
        
        // Calculate progress percentage
        $campaign['progress_percent'] = 0;
        if (isset($campaign['funding_goal']) && $campaign['funding_goal'] > 0) {
            $currentAmount = isset($campaign['current_amount']) ? $campaign['current_amount'] : 0;
            $campaign['progress_percent'] = min(100, round(($currentAmount / $campaign['funding_goal']) * 100));
        }
    }
    
} catch (PDOException $e) {
    error_log("Database error fetching campaigns: " . $e->getMessage());
    $error = 'A system error occurred while fetching campaigns. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Campaigns - CryptoFund</title>
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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10">
            <div>
                <h1 class="text-3xl font-bold mb-2 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">
                    Discover Campaigns
                </h1>
                <p class="text-gray-400">Browse and fund innovative blockchain projects</p>
            </div>
            
            <div class="mt-4 md:mt-0 flex items-center gap-2">
                <button class="bg-slate-800 hover:bg-slate-700 text-gray-300 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <button class="bg-slate-800 hover:bg-slate-700 text-gray-300 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-sort-amount-down mr-2"></i> Sort
                </button>
            </div>
        </div>
        
        <!-- Campaigns Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <?php if (empty($campaigns)): ?>
                <div class="col-span-full bg-slate-800/50 rounded-xl p-10 text-center">
                    <div class="text-gray-400 text-6xl mb-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-xl font-medium text-white mb-2">No Campaigns Found</h3>
                    <p class="text-gray-400 mb-6">There are no approved campaigns available at the moment.</p>
                    <button id="connectWalletBtn" class="connect-wallet-btn px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2 mx-auto">
                        <i class="fas fa-plus-circle"></i> Create Campaign
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($campaigns as $campaign): ?>
                <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                    <img src="<?php echo htmlspecialchars($campaign['thumbnail_url'] ?? 'https://images.unsplash.com/photo-1639988525250-a93576693f65'); ?>" 
                         alt="<?php echo htmlspecialchars($campaign['title']); ?>" 
                         class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-3">
                            <span class="bg-blue-600/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold">
                                <?php echo htmlspecialchars($campaign['category'] ?? 'General'); ?>
                            </span>
                            <?php if ($campaign['days_left'] < 5): ?>
                            <span class="text-gray-400 text-sm"><i class="fas fa-clock text-orange-400"></i> Ending Soon</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($campaign['title']); ?></h3>
                        <p class="text-gray-400 text-sm mb-4">
                            <?php echo mb_substr(htmlspecialchars($campaign['description']), 0, 100) . (mb_strlen($campaign['description']) > 100 ? '...' : ''); ?>
                        </p>
                        <div class="mb-4">
                            <div class="h-2 w-full bg-gray-700 rounded-full">
                                <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: <?php echo $campaign['progress_percent']; ?>%"></div>
                            </div>
                            <div class="flex justify-between text-sm mt-2">
                                <span class="text-blue-400 font-semibold">
                                    <?php echo isset($campaign['current_amount']) ? htmlspecialchars($campaign['current_amount']) : '0'; ?> 
                                    <?php echo htmlspecialchars($campaign['token_symbol'] ?? 'ETH'); ?> raised
                                </span>
                                <span class="text-gray-400">
                                    of <?php echo htmlspecialchars($campaign['funding_goal']); ?> 
                                    <?php echo htmlspecialchars($campaign['token_symbol'] ?? 'ETH'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 text-sm">
                                <i class="far fa-clock mr-1"></i> 
                                <?php echo $campaign['days_left']; ?> days left
                            </span>
                            <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($campaigns) && count($campaigns) > 9): ?>
        <div class="flex justify-center mt-8 mb-4">
            <nav class="flex items-center space-x-2">
                <a href="#" class="px-4 py-2 rounded-lg bg-slate-800 text-gray-400 hover:bg-slate-700 transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="#" class="px-4 py-2 rounded-lg bg-blue-600 text-white">1</a>
                <a href="#" class="px-4 py-2 rounded-lg bg-slate-800 text-gray-400 hover:bg-slate-700 transition">2</a>
                <a href="#" class="px-4 py-2 rounded-lg bg-slate-800 text-gray-400 hover:bg-slate-700 transition">3</a>
                <span class="px-4 py-2 text-gray-500">...</span>
                <a href="#" class="px-4 py-2 rounded-lg bg-slate-800 text-gray-400 hover:bg-slate-700 transition">10</a>
                <a href="#" class="px-4 py-2 rounded-lg bg-slate-800 text-gray-400 hover:bg-slate-700 transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </nav>
        </div>
        <?php endif; ?>
        
        <!-- Create Campaign CTA -->
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 border border-blue-500/30 rounded-xl p-8 shadow-lg mt-16">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-2/3">
                    <h2 class="text-3xl font-bold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">Start Your Own Blockchain Project</h2>
                    <p class="text-gray-300 mb-6">Have an innovative idea that needs funding? Connect your wallet and create a campaign to reach potential supporters in the blockchain community.</p>
                    <button id="connectWalletBtnCampaign" class="connect-wallet-btn px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-rocket"></i> Create Campaign
                    </button>
                </div>
                <div class="w-full md:w-1/3">
                    <img src="https://images.unsplash.com/photo-1516381548400-349d680edb56" alt="Create Campaign" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once 'includes/footer.php'; ?>
    
    <!-- Web3 Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.3/dist/web3.min.js"></script>
    <script src="https://unpkg.com/web3modal@1.9.8/dist/index.js"></script>
    <script src="https://unpkg.com/@walletconnect/web3-provider@1.7.8/dist/umd/index.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/wallet.js"></script>
    <script src="js/app.js"></script>
</body>
</html>