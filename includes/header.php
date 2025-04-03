<?php
// Get wallet connection status
$isWalletConnected = isset($_SESSION['wallet_connected']) && $_SESSION['wallet_connected'] === true;
$walletAddress = isset($_SESSION['wallet_address']) ? $_SESSION['wallet_address'] : '';

// Function to truncate Ethereum address
function truncateAddress($address) {
    if (!$address) return '';
    return substr($address, 0, 6) . '...' . substr($address, -4);
}
?>

<header class="bg-gray-900 backdrop-blur-lg sticky top-0 z-40 border-b border-gray-800/50">
    <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-3 group">
                <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl w-10 h-10 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:shadow-indigo-500/40 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-white">
                        <path d="M21 6.375c0 2.692-4.03 4.875-9 4.875S3 9.067 3 6.375 7.03 1.5 12 1.5s9 2.183 9 4.875z" />
                        <path d="M12 12.75c2.685 0 5.19-.586 7.078-1.609a8.283 8.283 0 001.897-1.384c.016.121.025.244.025.368C21 12.817 16.97 15 12 15s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.285 8.285 0 001.897 1.384C6.809 12.164 9.315 12.75 12 12.75z" />
                        <path d="M12 16.5c2.685 0 5.19-.586 7.078-1.609a8.282 8.282 0 001.897-1.384c.016.121.025.244.025.368 0 2.692-4.03 4.875-9 4.875s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.284 8.284 0 001.897 1.384C6.809 15.914 9.315 16.5 12 16.5z" />
                    </svg>
                </div>
                <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">CryptoFund</span>
            </a>
            
            <!-- Navigation -->
            <nav class="hidden md:flex items-center">
                <div class="bg-gray-800/50 rounded-full backdrop-blur-sm p-1 flex space-x-1 border border-gray-700/30">
                    <a href="index.php" class="px-4 py-2 rounded-full text-white hover:bg-gray-700 transition">Home</a>
                    <a href="campaigns.php" class="px-4 py-2 rounded-full text-white hover:bg-gray-700 transition">Explore</a>
                    <a href="create-campaign.php" class="px-4 py-2 rounded-full text-white hover:bg-gray-700 transition">Create</a>
                    <a href="#" class="px-4 py-2 rounded-full text-white hover:bg-gray-700 transition">About</a>
                </div>
                <a href="admin/index.php" class="ml-4 px-4 py-2 text-white hover:text-indigo-300 transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.75.75 0 00.674 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                    Admin
                </a>
            </nav>
            
            <!-- Wallet Connection -->
            <div class="flex items-center space-x-4">
                <!-- Connected Wallet Display (hidden by default) -->
                <div id="walletDisplay" class="relative wallet-dropdown hidden">
                    <button id="walletDropdownToggle" class="flex items-center gap-2 bg-gray-800/70 hover:bg-gray-700/70 border border-gray-700/30 text-white rounded-full px-4 py-2 transition-all duration-300">
                        <span class="h-2 w-2 rounded-full bg-green-400 shadow-lg shadow-green-400/50"></span>
                        <span id="walletAddressText" class="text-sm font-medium"><?php echo truncateAddress($walletAddress); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-gray-400">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <!-- Wallet Dropdown Menu -->
                    <div id="walletDropdown" class="absolute right-0 mt-2 w-60 bg-gray-800 rounded-xl shadow-xl border border-gray-700/50 hidden z-50 backdrop-blur-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-700/50 bg-gray-800/80">
                            <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Connected Wallet</p>
                            <div class="flex items-center justify-between">
                                <p id="walletBalanceText" class="text-gray-200 font-mono text-sm font-medium"><?php echo truncateAddress($walletAddress); ?></p>
                                <button id="copyAddressBtn" class="text-gray-400 hover:text-indigo-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                        <path d="M7 3.5A1.5 1.5 0 018.5 2h3.879a1.5 1.5 0 011.06.44l3.122 3.12A1.5 1.5 0 0117 6.622V12.5a1.5 1.5 0 01-1.5 1.5h-1v-3.379a3 3 0 00-.879-2.121L10.5 5.379A3 3 0 008.379 4.5H7v-1z" />
                                        <path d="M4.5 6A1.5 1.5 0 003 7.5v9A1.5 1.5 0 004.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L9.44 6.439A1.5 1.5 0 008.378 6H4.5z" />
                                    </svg>
                                </button>
                            </div>
                            <div id="networkDisplay" class="mt-2 flex items-center">
                                <div class="flex items-center gap-1">
                                    <span class="inline-block w-2 h-2 rounded-full bg-indigo-500"></span>
                                    <span class="text-xs text-gray-300">Ethereum Mainnet</span>
                                </div>
                            </div>
                        </div>
                        <div class="py-1">
                            <a href="profile.php" class="flex items-center gap-3 w-full text-left px-4 py-2 text-gray-300 hover:bg-gray-700/50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-indigo-400">
                                    <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                                </svg>
                                My Profile
                            </a>
                            <a href="profile.php#transactions" class="flex items-center gap-3 w-full text-left px-4 py-2 text-gray-300 hover:bg-gray-700/50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-indigo-400">
                                    <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                                </svg>
                                Transactions
                            </a>
                            <a href="#" class="flex items-center gap-3 w-full text-left px-4 py-2 text-gray-300 hover:bg-gray-700/50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-indigo-400">
                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54a6.993 6.993 0 01-1.929 1.115l-.33 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.05 7.05 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.51 3.456l.33-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                                Settings
                            </a>
                            <button id="disconnectWalletBtn" class="flex items-center gap-3 w-full text-left px-4 py-2 text-red-400 hover:bg-gray-700/50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                    <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd" />
                                    <path fill-rule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l1.048-.943a.75.75 0 10-1.004-1.114l-2.5 2.25a.75.75 0 000 1.114l2.5 2.25a.75.75 0 101.004-1.114l-1.048-.943h9.546A.75.75 0 0019 10z" clip-rule="evenodd" />
                                </svg>
                                Disconnect
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Connect Wallet Button -->
                <button id="connectWalletBtn" class="connect-wallet-btn group <?php echo $isWalletConnected ? 'connected' : ''; ?> relative overflow-hidden px-4 py-2 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-medium shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/40 transition-all duration-300 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path d="M1 4.25a3.733 3.733 0 012.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0016.75 2H3.25A2.25 2.25 0 001 4.25zM1 7.25a3.733 3.733 0 012.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0016.75 5H3.25A2.25 2.25 0 001 7.25zM7 8a1 1 0 011 1 2 2 0 104 0 1 1 0 011-1h3.75A2.25 2.25 0 0119 10.25v5.5A2.25 2.25 0 0116.75 18H3.25A2.25 2.25 0 011 15.75v-5.5A2.25 2.25 0 013.25 8H7z" />
                    </svg>
                    <span class="relative z-10">
                        <?php echo $isWalletConnected ? 'Connected' : 'Connect Wallet'; ?>
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-700 to-purple-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                </button>
                
                <!-- Mobile Menu Toggle -->
                <button id="mobileMenuToggle" class="md:hidden text-gray-300 hover:text-white p-1 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                        <path fill-rule="evenodd" d="M3 6.75A.75.75 0 013.75 6h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 6.75zM3 12a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 12zm0 5.25a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Navigation Menu (hidden by default) -->
<div id="mobileMenu" class="md:hidden bg-gray-900 border-b border-gray-800/50 hidden">
    <div class="container mx-auto px-4 py-2">
        <nav class="flex flex-col py-4 space-y-1">
            <a href="index.php" class="text-white hover:bg-gray-800 px-4 py-3 rounded-lg transition flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-indigo-400">
                    <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                </svg>
                Home
            </a>
            <a href="campaigns.php" class="text-white hover:bg-gray-800 px-4 py-3 rounded-lg transition flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-indigo-400">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                Explore
            </a>
            <a href="create-campaign.php" class="text-white hover:bg-gray-800 px-4 py-3 rounded-lg transition flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-indigo-400">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Create
            </a>
            <a href="#" class="text-white hover:bg-gray-800 px-4 py-3 rounded-lg transition flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-indigo-400">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                </svg>
                About
            </a>
            <a href="profile.php" class="text-white hover:bg-gray-800 px-4 py-3 rounded-lg transition flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-indigo-400">
                    <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                </svg>
                My Profile
            </a>
            <a href="admin/index.php" class="text-white hover:bg-gray-800 px-4 py-3 rounded-lg transition flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-indigo-400">
                    <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.75.75 0 00.674 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                </svg>
                Admin
            </a>
        </nav>
    </div>
</div>
