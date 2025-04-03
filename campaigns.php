<?php
session_start();
include_once 'includes/db.php';

// Get wallet connection status
$isWalletConnected = isset($_SESSION['wallet_connected']) && $_SESSION['wallet_connected'] === true;
$walletAddress = isset($_SESSION['wallet_address']) ? $_SESSION['wallet_address'] : '';

// Query to get campaigns (sample data for now)
$stmt = $pdo->query("SELECT * FROM campaigns WHERE status = 'active' ORDER BY created_at DESC LIMIT 9");
$campaigns = [];

// Check if we have campaigns in the database
if ($stmt && $stmt->rowCount() > 0) {
    $campaigns = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Campaigns - CryptoFund</title>
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
        <section class="mb-12">
            <h1 class="text-4xl font-bold mb-4">Explore Campaigns</h1>
            <p class="text-xl text-gray-300">Discover innovative projects seeking funding on the blockchain.</p>
        </section>

        <!-- Filters & Search -->
        <section class="mb-12">
            <div class="bg-slate-800/50 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-gray-400 text-sm mb-2">Search</label>
                        <div class="relative">
                            <input type="text" placeholder="Search campaigns..." class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                            <span class="absolute right-3 top-3 text-gray-400">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-gray-400 text-sm mb-2">Category</label>
                        <select class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <option value="technology">Technology</option>
                            <option value="environment">Environment</option>
                            <option value="education">Education</option>
                            <option value="art">Art & Creative</option>
                            <option value="humanitarian">Humanitarian</option>
                            <option value="defi">DeFi</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-gray-400 text-sm mb-2">Sort By</label>
                        <select class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="newest">Newest First</option>
                            <option value="funding">Most Funded</option>
                            <option value="ending">Ending Soon</option>
                            <option value="trending">Trending</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-300 flex items-center gap-2">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Campaigns Grid -->
        <section class="mb-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (count($campaigns) > 0): ?>
                    <?php foreach ($campaigns as $campaign): ?>
                        <!-- Campaign Card -->
                        <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                            <img src="<?php echo htmlspecialchars($campaign['thumbnail_url']); ?>" alt="Campaign" class="w-full h-48 object-cover">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="bg-blue-600/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold"><?php echo htmlspecialchars($campaign['category']); ?></span>
                                    <span class="text-gray-400 text-sm"><i class="far fa-clock"></i> <?php echo htmlspecialchars($campaign['end_date']); ?></span>
                                </div>
                                <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($campaign['title']); ?></h3>
                                <p class="text-gray-400 text-sm mb-4"><?php echo substr(htmlspecialchars($campaign['description']), 0, 100) . '...'; ?></p>
                                <div class="mb-4">
                                    <div class="h-2 w-full bg-gray-700 rounded-full">
                                        <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: 65%"></div>
                                    </div>
                                    <div class="flex justify-between text-sm mt-2">
                                        <span class="text-blue-400 font-semibold">65 ETH raised</span>
                                        <span class="text-gray-400">of 100 ETH</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400 text-sm"><i class="fas fa-users mr-1"></i> 42 backers</span>
                                    <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Sample Campaign Cards since we don't have database data yet -->
                    <!-- Campaign 1 -->
                    <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1591901206069-ed60c4429a2e" alt="Campaign" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-blue-600/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold">Technology</span>
                                <span class="text-gray-400 text-sm"><i class="fas fa-fire text-orange-400"></i> Trending</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Eco-Friendly Blockchain Solution</h3>
                            <p class="text-gray-400 text-sm mb-4">Developing a carbon-neutral blockchain network for sustainable transactions.</p>
                            <div class="mb-4">
                                <div class="h-2 w-full bg-gray-700 rounded-full">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: 75%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-blue-400 font-semibold">75 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 text-sm"><i class="far fa-clock mr-1"></i> 15 days left</span>
                                <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campaign 2 -->
                    <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1591901206025-cf902bf74f22" alt="Campaign" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-green-600/20 text-green-400 px-3 py-1 rounded-full text-xs font-semibold">Environment</span>
                                <span class="text-gray-400 text-sm"><i class="fas fa-users text-yellow-400"></i> Popular</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Ocean Cleanup Initiative</h3>
                            <p class="text-gray-400 text-sm mb-4">Using blockchain to track and fund ocean plastic removal worldwide.</p>
                            <div class="mb-4">
                                <div class="h-2 w-full bg-gray-700 rounded-full">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: 40%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-blue-400 font-semibold">40 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 text-sm"><i class="far fa-clock mr-1"></i> 30 days left</span>
                                <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campaign 3 -->
                    <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1591901206004-1b3cc4ffbe3c" alt="Campaign" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-purple-600/20 text-purple-400 px-3 py-1 rounded-full text-xs font-semibold">Education</span>
                                <span class="text-gray-400 text-sm"><i class="fas fa-star text-yellow-400"></i> New</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Blockchain Academy</h3>
                            <p class="text-gray-400 text-sm mb-4">Free education platform teaching blockchain development to underserved communities.</p>
                            <div class="mb-4">
                                <div class="h-2 w-full bg-gray-700 rounded-full">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: 25%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-blue-400 font-semibold">25 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 text-sm"><i class="far fa-clock mr-1"></i> 45 days left</span>
                                <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campaign 4 -->
                    <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1591901206107-a81f5b57e7f3" alt="Campaign" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-yellow-600/20 text-yellow-400 px-3 py-1 rounded-full text-xs font-semibold">Art</span>
                                <span class="text-gray-400 text-sm"><i class="fas fa-rocket text-purple-400"></i> Featured</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">NFT Art Collective</h3>
                            <p class="text-gray-400 text-sm mb-4">Supporting artists from developing countries to create and sell NFT artwork.</p>
                            <div class="mb-4">
                                <div class="h-2 w-full bg-gray-700 rounded-full">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: 85%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-blue-400 font-semibold">85 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 text-sm"><i class="far fa-clock mr-1"></i> 5 days left</span>
                                <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campaign 5 -->
                    <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1639987402632-d7273e921454" alt="Campaign" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-red-600/20 text-red-400 px-3 py-1 rounded-full text-xs font-semibold">Humanitarian</span>
                                <span class="text-gray-400 text-sm"><i class="fas fa-heart text-red-400"></i> Urgent</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Emergency Relief DAO</h3>
                            <p class="text-gray-400 text-sm mb-4">Blockchain-powered relief fund for disaster zones with transparent fund distribution.</p>
                            <div class="mb-4">
                                <div class="h-2 w-full bg-gray-700 rounded-full">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: 50%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-blue-400 font-semibold">50 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 text-sm"><i class="far fa-clock mr-1"></i> 20 days left</span>
                                <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campaign 6 -->
                    <div class="campaign-card bg-slate-800/50 rounded-xl overflow-hidden shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1640143405373-bbb919afa0da" alt="Campaign" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-indigo-600/20 text-indigo-400 px-3 py-1 rounded-full text-xs font-semibold">DeFi</span>
                                <span class="text-gray-400 text-sm"><i class="fas fa-bolt text-yellow-400"></i> Fast Funding</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Decentralized Lending Protocol</h3>
                            <p class="text-gray-400 text-sm mb-4">Open-source lending platform making finance accessible to the unbanked.</p>
                            <div class="mb-4">
                                <div class="h-2 w-full bg-gray-700 rounded-full">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-purple-600 to-blue-600" style="width: 65%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-blue-400 font-semibold">65 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 text-sm"><i class="far fa-clock mr-1"></i> 10 days left</span>
                                <a href="#" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-blue-700 transition">Fund Now</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Load More Button -->
            <div class="text-center mt-12">
                <button class="px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300">
                    Load More Campaigns
                </button>
            </div>
        </section>

        <!-- Call to Action -->
        <section>
            <div class="bg-gradient-to-r from-slate-900 to-blue-900/30 border border-blue-500/30 rounded-xl p-8 shadow-lg">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div>
                        <h2 class="text-3xl font-bold mb-4">Have a Great Idea?</h2>
                        <p class="text-xl text-gray-300 mb-6">Start your own campaign and bring your vision to life with blockchain-powered funding.</p>
                        <button class="px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i> Create Campaign
                        </button>
                    </div>
                    <div class="w-full md:w-1/3">
                        <img src="https://images.unsplash.com/photo-1640592276475-56a1c277a38f" alt="Create Campaign" class="rounded-lg shadow-xl">
                    </div>
                </div>
            </div>
        </section>
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

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50"></div>

    <?php include_once 'includes/footer.php'; ?>

    <!-- Web3 Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.4/dist/web3.min.js"></script>
    <script src="https://unpkg.com/@walletconnect/web3-provider@1.7.8/dist/umd/index.min.js"></script>
    <script src="https://unpkg.com/web3modal@1.9.9/dist/index.js"></script>
    
    <!-- Custom JS -->
    <script src="js/wallet.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
