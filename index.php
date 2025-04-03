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
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <?php include_once 'includes/header.php'; ?>

    <main>
        <!-- Hero Section with Animated Background -->
        <section class="relative overflow-hidden py-20 sm:py-28">
            <!-- Animated background -->
            <div class="absolute inset-0 bg-grid-slate-900/[0.04] bg-[bottom_1px_center] dark:bg-grid-slate-400/[0.05] dark:bg-bottom dark:border-b dark:border-slate-100/5"></div>
            <div class="absolute top-0 left-0 right-0 h-[500px] bg-gradient-to-br from-indigo-600/20 via-purple-600/10 to-transparent blur-3xl"></div>
            
            <div class="container mx-auto px-4 relative z-10 flex flex-col md:flex-row items-center">
                <div class="w-full md:w-1/2 mb-12 md:mb-0 pr-0 md:pr-12">
                    <div class="animate__animated animate__fadeInUp">
                        <div class="flex items-center mb-6 space-x-2">
                            <div class="bg-indigo-600/10 py-1 px-3 rounded-full">
                                <div class="text-xs font-medium text-indigo-400 flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-indigo-400 mr-2 animate-pulse"></span>
                                    Web3 Crowdfunding
                                </div>
                            </div>
                        </div>
                        
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 tracking-tight">
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Decentralized Funding</span>
                            <span class="block text-white">For Innovative Projects</span>
                        </h1>
                        
                        <p class="text-lg text-gray-300 mb-8 max-w-lg">
                            Empower creators and support revolutionary ideas with secure, transparent blockchain-powered fundraising on multiple networks.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button id="connectWalletBtn" class="connect-wallet-btn relative overflow-hidden group rounded-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                    <path d="M1 4.25a3.733 3.733 0 012.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0016.75 2H3.25A2.25 2.25 0 001 4.25zM1 7.25a3.733 3.733 0 012.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0016.75 5H3.25A2.25 2.25 0 001 7.25zM7 8a1 1 0 011 1 2 2 0 104 0 1 1 0 011-1h3.75A2.25 2.25 0 0119 10.25v5.5A2.25 2.25 0 0116.75 18H3.25A2.25 2.25 0 011 15.75v-5.5A2.25 2.25 0 013.25 8H7z" />
                                </svg>
                                <span class="relative z-10">Connect Wallet</span>
                                <span class="absolute inset-0 bg-gradient-to-r from-indigo-700 to-purple-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            </button>
                            <a href="campaigns.php" class="rounded-full px-6 py-3 bg-gray-800/80 hover:bg-gray-700/80 border border-gray-700/50 text-white font-medium transition-colors duration-300 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-indigo-400">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                Explore Campaigns
                            </a>
                        </div>
                        
                        <div class="mt-12 flex items-center space-x-6">
                            <div class="flex -space-x-2">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-10 h-10 rounded-full border-2 border-gray-900" alt="User">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-10 h-10 rounded-full border-2 border-gray-900" alt="User">
                                <img src="https://randomuser.me/api/portraits/men/46.jpg" class="w-10 h-10 rounded-full border-2 border-gray-900" alt="User">
                                <div class="w-10 h-10 rounded-full bg-gray-800 border-2 border-gray-900 flex items-center justify-center text-xs font-medium">250+</div>
                            </div>
                            <div class="text-sm text-gray-400">
                                Trusted by <span class="text-indigo-400 font-medium">250+</span> creators worldwide
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="w-full md:w-1/2 animate__animated animate__fadeInUp">
                    <div class="relative p-2 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl rotate-1 transform hover:rotate-0 transition-transform duration-500">
                        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-indigo-600/40 rounded-full blur-xl"></div>
                        <div class="absolute -top-6 -right-6 w-32 h-32 bg-purple-600/40 rounded-full blur-xl"></div>
                        
                        <div class="relative bg-gray-900 rounded-xl overflow-hidden">
                            <img 
                                src="https://images.unsplash.com/photo-1621761191319-c6fb62004040?ixlib=rb-4.0.3" 
                                alt="Crypto Crowdfunding" 
                                class="w-full rounded-xl shadow-2xl">
                                
                            <!-- Overlay stats -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-gray-900 to-transparent p-6">
                                <div class="flex justify-between items-end">
                                    <div>
                                        <h3 class="text-xl font-bold text-white mb-1">Platform Statistics</h3>
                                        <p class="text-gray-300 text-sm">Last 30 days</p>
                                    </div>
                                    <div class="flex space-x-4">
                                        <div class="text-right">
                                            <p class="text-sm text-gray-400">Raised</p>
                                            <p class="text-xl font-bold text-indigo-400">2,543 ETH</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-400">Projects</p>
                                            <p class="text-xl font-bold text-indigo-400">128</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container mx-auto px-4 mt-24">
                <div class="flex flex-wrap justify-center gap-4 md:gap-10">
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-5 flex items-center space-x-4 border border-gray-700/30">
                        <div class="w-12 h-12 bg-indigo-500/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-indigo-400">
                                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">$12.5M+</div>
                            <div class="text-sm text-gray-400">Funds Raised</div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-5 flex items-center space-x-4 border border-gray-700/30">
                        <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-purple-400">
                                <path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">250K+</div>
                            <div class="text-sm text-gray-400">User Community</div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-5 flex items-center space-x-4 border border-gray-700/30">
                        <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-blue-400">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 01-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.513 1.324 1.513 3.518 0 4.842a3.75 3.75 0 01-.837.552c-.676.328-1.028.774-1.028 1.152v.75a.75.75 0 01-1.5 0v-.75c0-1.279 1.06-2.107 1.875-2.502.182-.088.351-.199.503-.331.83-.727.83-1.857 0-2.584zM12 18a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">1,840+</div>
                            <div class="text-sm text-gray-400">Projects Funded</div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-5 flex items-center space-x-4 border border-gray-700/30">
                        <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-green-400">
                                <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">93%</div>
                            <div class="text-sm text-gray-400">Success Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Features Section -->
        <section class="py-24 bg-gray-950 relative overflow-hidden">
            <div class="absolute inset-0 bg-grid-slate-900/[0.04] bg-[bottom_1px_center] dark:bg-grid-slate-400/[0.05] dark:bg-bottom dark:border-b dark:border-slate-100/5"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-xl mx-auto text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">A Better Way to Raise Funds</h2>
                    <p class="text-gray-400 text-lg">Cutting-edge features that make your fundraising journey smooth, secure, and successful.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-6 transform transition duration-500 hover:scale-105">
                        <div class="w-14 h-14 mb-6 bg-indigo-500/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-indigo-400">
                                <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.75.75 0 00.674 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Secure Connections</h3>
                        <p class="text-gray-400 mb-4">Connect securely to multiple blockchain networks with just a few clicks. Your funds and data remain yours.</p>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            <span class="bg-indigo-900/30 text-indigo-400 text-xs px-2.5 py-1 rounded-md">MetaMask</span>
                            <span class="bg-indigo-900/30 text-indigo-400 text-xs px-2.5 py-1 rounded-md">Coinbase</span>
                            <span class="bg-indigo-900/30 text-indigo-400 text-xs px-2.5 py-1 rounded-md">Trust Wallet</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-6 transform transition duration-500 hover:scale-105">
                        <div class="w-14 h-14 mb-6 bg-purple-500/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-purple-400">
                                <path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z" />
                                <path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 14.625v-9.75zM8.25 9.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM18.75 9a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75V9.75a.75.75 0 00-.75-.75h-.008zM4.5 9.75A.75.75 0 015.25 9h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V9.75z" clip-rule="evenodd" />
                                <path d="M2.25 18a.75.75 0 000 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 00-.75-.75H2.25z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Transparent Funding</h3>
                        <p class="text-gray-400 mb-4">Every transaction is recorded on the blockchain, providing complete transparency and traceability for backers.</p>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            <span class="bg-purple-900/30 text-purple-400 text-xs px-2.5 py-1 rounded-md">Ethereum</span>
                            <span class="bg-purple-900/30 text-purple-400 text-xs px-2.5 py-1 rounded-md">Polygon</span>
                            <span class="bg-purple-900/30 text-purple-400 text-xs px-2.5 py-1 rounded-md">BSC</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-6 transform transition duration-500 hover:scale-105">
                        <div class="w-14 h-14 mb-6 bg-blue-500/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-blue-400">
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM6.262 6.072a8.25 8.25 0 1010.562-.766 4.5 4.5 0 01-1.318 1.357L14.25 7.5l.165.33a.809.809 0 01-1.086 1.085l-.604-.302a1.125 1.125 0 00-1.298.21l-.132.131c-.439.44-.439 1.152 0 1.591l.296.296c.256.257.622.374.98.314l1.17-.195c.323-.054.654.036.905.245l1.33 1.108c.32.267.46.694.358 1.1a8.7 8.7 0 01-2.288 4.04l-.723.724a1.125 1.125 0 01-1.298.21l-.153-.076a1.125 1.125 0 01-.622-1.006v-1.089c0-.298-.119-.585-.33-.796l-1.347-1.347a1.125 1.125 0 01-.21-1.298L9.75 12l-1.64-1.64a6 6 0 01-1.676-3.257l-.172-1.03z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Global Access</h3>
                        <p class="text-gray-400 mb-4">Fund or create campaigns from anywhere in the world with no geographical restrictions or currency conversion headaches.</p>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            <span class="bg-blue-900/30 text-blue-400 text-xs px-2.5 py-1 rounded-md">Worldwide</span>
                            <span class="bg-blue-900/30 text-blue-400 text-xs px-2.5 py-1 rounded-md">No Limits</span>
                            <span class="bg-blue-900/30 text-blue-400 text-xs px-2.5 py-1 rounded-md">24/7 Access</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-6 transform transition duration-500 hover:scale-105">
                        <div class="w-14 h-14 mb-6 bg-green-500/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-green-400">
                                <path d="M4.5 3.75a3 3 0 00-3 3v.75h21v-.75a3 3 0 00-3-3h-15z" />
                                <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 003 3h15a3 3 0 003-3v-7.5zm-18 3.75a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Lower Fees</h3>
                        <p class="text-gray-400 mb-4">Cut out intermediaries and save on platform fees compared to traditional crowdfunding platforms.</p>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            <span class="bg-green-900/30 text-green-400 text-xs px-2.5 py-1 rounded-md">3% Platform Fee</span>
                            <span class="bg-green-900/30 text-green-400 text-xs px-2.5 py-1 rounded-md">No Hidden Costs</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-6 transform transition duration-500 hover:scale-105">
                        <div class="w-14 h-14 mb-6 bg-yellow-500/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-yellow-400">
                                <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6zm4.5 7.5a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0v-2.25a.75.75 0 01.75-.75zm3.75-1.5a.75.75 0 00-1.5 0v4.5a.75.75 0 001.5 0V12zm2.25-3a.75.75 0 01.75.75v6.75a.75.75 0 01-1.5 0V9.75A.75.75 0 0113.5 9zm3.75-1.5a.75.75 0 00-1.5 0v9a.75.75 0 001.5 0v-9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Real-Time Analytics</h3>
                        <p class="text-gray-400 mb-4">Track your campaign's performance with detailed analytics and insights to help you reach your funding goals.</p>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            <span class="bg-yellow-900/30 text-yellow-400 text-xs px-2.5 py-1 rounded-md">Live Updates</span>
                            <span class="bg-yellow-900/30 text-yellow-400 text-xs px-2.5 py-1 rounded-md">Detailed Reports</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-6 transform transition duration-500 hover:scale-105">
                        <div class="w-14 h-14 mb-6 bg-pink-500/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-pink-400">
                                <path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Community Building</h3>
                        <p class="text-gray-400 mb-4">Connect with backers directly, build your community, and engage with supporters throughout your funding journey.</p>
                        <div class="flex flex-wrap gap-2 mt-auto">
                            <span class="bg-pink-900/30 text-pink-400 text-xs px-2.5 py-1 rounded-md">Direct Messaging</span>
                            <span class="bg-pink-900/30 text-pink-400 text-xs px-2.5 py-1 rounded-md">Updates</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Featured Campaigns -->
        <section class="py-24 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-gray-900 to-gray-950"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="flex justify-between items-center mb-12">
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Featured Campaigns</h2>
                    <a href="campaigns.php" class="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-2">
                        View All 
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Campaign 1 -->
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl overflow-hidden group hover:border-indigo-500/30 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/10">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1639152201720-5e536d254d81" alt="Eco-Friendly Blockchain" class="w-full h-56 object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent opacity-60"></div>
                            <div class="absolute top-4 left-4 bg-indigo-600/20 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium">Technology</div>
                            <div class="absolute top-4 right-4 bg-orange-500/20 text-orange-400 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-orange-400 animate-pulse"></span> Trending
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-indigo-400 transition-colors">Eco-Friendly Blockchain Solution</h3>
                            <p class="text-gray-400 text-sm mb-4">Developing a carbon-neutral blockchain network for sustainable transactions.</p>
                            
                            <div class="mb-6">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-indigo-400 font-medium">75 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                                <div class="h-2 w-full bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-purple-600" style="width: 75%"></div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-1.5 text-sm text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-indigo-400">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                    </svg>
                                    15 days left
                                </div>
                                <a href="#" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Fund Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campaign 2 -->
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl overflow-hidden group hover:border-indigo-500/30 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/10">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9" alt="Ocean Cleanup" class="w-full h-56 object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent opacity-60"></div>
                            <div class="absolute top-4 left-4 bg-green-600/20 text-green-400 px-3 py-1 rounded-full text-xs font-medium">Environment</div>
                            <div class="absolute top-4 right-4 bg-yellow-500/20 text-yellow-400 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                </svg>
                                Popular
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-indigo-400 transition-colors">Ocean Cleanup Initiative</h3>
                            <p class="text-gray-400 text-sm mb-4">Using blockchain to track and fund ocean plastic removal worldwide.</p>
                            
                            <div class="mb-6">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-indigo-400 font-medium">40 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                                <div class="h-2 w-full bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-purple-600" style="width: 40%"></div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-1.5 text-sm text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-indigo-400">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                    </svg>
                                    30 days left
                                </div>
                                <a href="#" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Fund Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campaign 3 -->
                    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl overflow-hidden group hover:border-indigo-500/30 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/10">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1610563166150-b34df4f3bcd6" alt="Blockchain Academy" class="w-full h-56 object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent opacity-60"></div>
                            <div class="absolute top-4 left-4 bg-purple-600/20 text-purple-400 px-3 py-1 rounded-full text-xs font-medium">Education</div>
                            <div class="absolute top-4 right-4 bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                    <path d="M15.98 1.804a1 1 0 00-1.96 0l-.24 1.192a1 1 0 01-.784.785l-1.192.238a1 1 0 000 1.962l1.192.238a1 1 0 01.785.785l.238 1.192a1 1 0 001.962 0l.238-1.192a1 1 0 01.785-.785l1.192-.238a1 1 0 000-1.962l-1.192-.238a1 1 0 01-.785-.785l-.238-1.192zM6.949 5.684a1 1 0 00-1.898 0l-.683 2.051a1 1 0 01-.633.633l-2.051.683a1 1 0 000 1.898l2.051.684a1 1 0 01.633.632l.683 2.051a1 1 0 001.898 0l.683-2.051a1 1 0 01.633-.633l2.051-.683a1 1 0 000-1.898l-2.051-.683a1 1 0 01-.633-.633L6.95 5.684zM13.949 13.684a1 1 0 00-1.898 0l-.184.551a1 1 0 01-.632.633l-.551.183a1 1 0 000 1.898l.551.183a1 1 0 01.633.633l.183.551a1 1 0 001.898 0l.184-.551a1 1 0 01.632-.633l.551-.183a1 1 0 000-1.898l-.551-.184a1 1 0 01-.633-.632l-.183-.551z" />
                                </svg>
                                New
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2 group-hover:text-indigo-400 transition-colors">Blockchain Academy</h3>
                            <p class="text-gray-400 text-sm mb-4">Free education platform teaching blockchain development to underserved communities.</p>
                            
                            <div class="mb-6">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-indigo-400 font-medium">25 ETH raised</span>
                                    <span class="text-gray-400">of 100 ETH</span>
                                </div>
                                <div class="h-2 w-full bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-purple-600" style="width: 25%"></div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-1.5 text-sm text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-indigo-400">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                    </svg>
                                    45 days left
                                </div>
                                <a href="#" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Fund Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Wallet Connection Guide -->
        <section class="py-24 bg-gradient-to-b from-gray-950 to-gray-900 relative">
            <div class="container mx-auto px-4">
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 border border-gray-800 rounded-3xl overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
                    <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-purple-600/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>
                    
                    <div class="relative p-8 md:p-12 lg:p-16 z-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                            <div>
                                <h2 class="text-3xl md:text-4xl font-bold mb-6 bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Connect Your Wallet <br>in Seconds</h2>
                                <p class="text-gray-300 mb-8">Our streamlined wallet connection process makes it easy to start funding or creating campaigns with just a few clicks.</p>
                                
                                <div class="space-y-6">
                                    <div class="flex items-start gap-4">
                                        <div class="w-8 h-8 rounded-full bg-indigo-600/20 flex items-center justify-center text-indigo-400 font-bold flex-shrink-0">1</div>
                                        <div>
                                            <h3 class="font-semibold text-xl mb-1">Click "Connect Wallet"</h3>
                                            <p class="text-gray-400">Use the connect wallet button in the header or anywhere on the page to start.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start gap-4">
                                        <div class="w-8 h-8 rounded-full bg-indigo-600/20 flex items-center justify-center text-indigo-400 font-bold flex-shrink-0">2</div>
                                        <div>
                                            <h3 class="font-semibold text-xl mb-1">Approve Connection</h3>
                                            <p class="text-gray-400">Confirm the connection request in your wallet (MetaMask, Trust Wallet, etc.)</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start gap-4">
                                        <div class="w-8 h-8 rounded-full bg-indigo-600/20 flex items-center justify-center text-indigo-400 font-bold flex-shrink-0">3</div>
                                        <div>
                                            <h3 class="font-semibold text-xl mb-1">Start Funding or Creating</h3>
                                            <p class="text-gray-400">That's it! You're ready to support projects or create your own campaign.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <button id="connectWalletBtnSecondary" class="connect-wallet-btn mt-10 relative overflow-hidden group rounded-full px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path d="M1 4.25a3.733 3.733 0 012.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0016.75 2H3.25A2.25 2.25 0 001 4.25zM1 7.25a3.733 3.733 0 012.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0016.75 5H3.25A2.25 2.25 0 001 7.25zM7 8a1 1 0 011 1 2 2 0 104 0 1 1 0 011-1h3.75A2.25 2.25 0 0119 10.25v5.5A2.25 2.25 0 0116.75 18H3.25A2.25 2.25 0 011 15.75v-5.5A2.25 2.25 0 013.25 8H7z" />
                                    </svg>
                                    <span class="relative z-10">Connect Your Wallet</span>
                                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-700 to-purple-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                                </button>
                            </div>
                            
                            <div class="relative">
                                <div class="relative p-3 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl rotate-1 transform hover:rotate-0 transition-transform duration-500">
                                    <img 
                                        src="https://images.unsplash.com/photo-1642058816443-c6554e6f0fd5?ixlib=rb-4.0.3" 
                                        alt="Cryptocurrency Wallet" 
                                        class="rounded-lg relative z-10 h-full object-cover bg-gray-900">
                                </div>
                                
                                <!-- Floating elements -->
                                <div class="absolute top-10 -left-10 p-4 bg-gray-800/90 backdrop-blur-sm rounded-xl border border-gray-700/50 shadow-xl w-64 transform -rotate-6 hidden md:block">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-white">
                                                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-400 text-xs">Your support matters</div>
                                            <div class="text-white font-medium">85% directly to creators</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="absolute bottom-10 -right-10 p-4 bg-gray-800/90 backdrop-blur-sm rounded-xl border border-gray-700/50 shadow-xl w-64 transform rotate-6 hidden md:block">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-white">
                                                <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-400 text-xs">Secure & trustless</div>
                                            <div class="text-white font-medium">No intermediaries</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    
    <!-- Use specific library versions for better compatibility -->
    <script src="https://unpkg.com/@walletconnect/web3-provider@1.7.8/dist/umd/index.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/web3modal@1.9.9/dist/index.min.js"></script>
    
    <!-- Custom JS files -->
    <script src="js/wallet.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
