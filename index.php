<?php
session_start();
include_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoFund - Web3 Crowdfunding Platform</title>
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
        <!-- Hero Section -->
        <section id="hero" class="flex flex-col md:flex-row items-center justify-between py-12 md:py-20 gap-8">
            <div class="w-full md:w-1/2 animate__animated animate__fadeInLeft">
                <h1 class="text-4xl md:text-5xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">
                    Decentralized Crowdfunding for the Future
                </h1>
                <p class="text-xl text-gray-300 mb-8">
                    Connect your wallet to start funding or creating blockchain-powered campaigns.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button id="connectWalletBtn" class="connect-wallet-btn px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-wallet"></i> Connect Wallet
                    </button>
                    <a href="campaigns.php" class="px-8 py-3 rounded-lg bg-transparent border-2 border-blue-500 text-white hover:bg-blue-500/20 font-semibold transition duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-rocket"></i> Explore Campaigns
                    </a>
                </div>
            </div>
            <div class="w-full md:w-1/2 animate__animated animate__fadeInRight">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1639987402632-d7273e921454" alt="Wallet Interface" class="rounded-xl shadow-2xl w-full">
                    <div class="absolute -bottom-4 -right-4 bg-blue-600 rounded-full p-4 shadow-lg">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-16">
            <h2 class="text-3xl font-bold text-center mb-12 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">Why Choose CryptoFund?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-slate-800/50 p-6 rounded-xl shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Secure Connections</h3>
                    <p class="text-gray-400">Connect with multiple wallet providers securely using Web3Modal integration.</p>
                </div>
                <div class="bg-slate-800/50 p-6 rounded-xl shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Transparent Funding</h3>
                    <p class="text-gray-400">All transactions are recorded on the blockchain ensuring complete transparency.</p>
                </div>
                <div class="bg-slate-800/50 p-6 rounded-xl shadow-lg hover:shadow-blue-500/20 hover:translate-y-[-5px] transition-all duration-300">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-globe text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Global Access</h3>
                    <p class="text-gray-400">Anyone with a crypto wallet can participate in funding campaigns from anywhere.</p>
                </div>
            </div>
        </section>

        <!-- Featured Campaigns Section -->
        <section id="featured-campaigns" class="py-16">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">Featured Campaigns</h2>
                <a href="campaigns.php" class="text-blue-400 hover:text-blue-300 transition">View All <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
            </div>
        </section>

        <!-- Wallet Connection Info Section -->
        <section id="wallet-info" class="py-12">
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 border border-blue-500/30 rounded-xl p-8 shadow-lg">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/2">
                        <img src="https://images.unsplash.com/photo-1640161704729-cbe966a08476" alt="Cryptocurrency Wallet" class="rounded-lg shadow-xl">
                    </div>
                    <div class="w-full md:w-1/2">
                        <h2 class="text-3xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">How to Connect Your Wallet</h2>
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="bg-blue-600 rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="font-bold">1</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-1">Click "Connect Wallet"</h3>
                                    <p class="text-gray-400">Use the connect wallet button in the header or on the page.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="bg-blue-600 rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="font-bold">2</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-1">Choose Your Provider</h3>
                                    <p class="text-gray-400">Select from popular providers like MetaMask, WalletConnect, or Coinbase Wallet.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="bg-blue-600 rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="font-bold">3</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-1">Authorize Connection</h3>
                                    <p class="text-gray-400">Approve the connection request in your wallet.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="bg-blue-600 rounded-full h-8 w-8 flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="font-bold">4</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-1">Start Funding</h3>
                                    <p class="text-gray-400">Once connected, you can fund campaigns or create your own.</p>
                                </div>
                            </div>
                        </div>
                        <button id="connectWalletBtnSecondary" class="mt-8 connect-wallet-btn px-8 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-wallet"></i> Connect Your Wallet
                        </button>
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
