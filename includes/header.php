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

<header class="bg-slate-900/80 backdrop-blur-sm sticky top-0 z-40 border-b border-slate-800">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-2">
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 w-10 h-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cube text-white text-xl"></i>
                </div>
                <span class="text-2xl font-bold text-white">CryptoFund</span>
            </a>
            
            <!-- Navigation -->
            <nav class="hidden md:flex space-x-6">
                <a href="index.php" class="text-gray-300 hover:text-white transition">Home</a>
                <a href="campaigns.php" class="text-gray-300 hover:text-white transition">Campaigns</a>
                <a href="#" class="text-gray-300 hover:text-white transition">How It Works</a>
                <a href="#" class="text-gray-300 hover:text-white transition">About</a>
            </nav>
            
            <!-- Wallet Connection -->
            <div class="flex items-center space-x-4">
                <!-- Connected Wallet Display (hidden by default) -->
                <div id="walletDisplay" class="wallet-dropdown hidden">
                    <button id="walletDropdownToggle" class="flex items-center space-x-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg px-3 py-2 transition">
                        <span class="bg-green-500 w-2 h-2 rounded-full"></span>
                        <span id="walletAddressText"><?php echo truncateAddress($walletAddress); ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    
                    <!-- Wallet Dropdown Menu -->
                    <div id="walletDropdownMenu" class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-lg shadow-xl border border-slate-700 hidden">
                        <div class="p-3 border-b border-slate-700">
                            <p class="text-gray-400 text-xs">Connected Wallet</p>
                            <p class="text-blue-400 font-mono text-sm"><?php echo truncateAddress($walletAddress); ?></p>
                        </div>
                        <a href="#" class="block px-4 py-2 text-gray-300 hover:bg-slate-700 transition">
                            <i class="fas fa-user mr-2"></i> My Profile
                        </a>
                        <a href="#" class="block px-4 py-2 text-gray-300 hover:bg-slate-700 transition">
                            <i class="fas fa-history mr-2"></i> Transactions
                        </a>
                        <a href="#" class="block px-4 py-2 text-gray-300 hover:bg-slate-700 transition">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                        <button id="disconnectWalletBtn" class="w-full text-left px-4 py-2 text-red-400 hover:bg-slate-700 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i> Disconnect
                        </button>
                    </div>
                </div>
                
                <!-- Connect Wallet Button -->
                <button id="connectWalletBtn" class="connect-wallet-btn <?php echo $isWalletConnected ? 'connected' : ''; ?> px-4 py-2 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold shadow-lg transition duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-wallet"></i> 
                    <?php echo $isWalletConnected ? 'Connected' : 'Connect Wallet'; ?>
                </button>
                
                <!-- Mobile Menu Toggle -->
                <button class="md:hidden text-gray-300 hover:text-white">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Navigation Menu (hidden by default) -->
<div class="md:hidden bg-slate-900 border-b border-slate-800 hidden">
    <div class="container mx-auto px-4 py-2">
        <nav class="flex flex-col space-y-3 py-4">
            <a href="index.php" class="text-gray-300 hover:text-white transition py-2">Home</a>
            <a href="campaigns.php" class="text-gray-300 hover:text-white transition py-2">Campaigns</a>
            <a href="#" class="text-gray-300 hover:text-white transition py-2">How It Works</a>
            <a href="#" class="text-gray-300 hover:text-white transition py-2">About</a>
        </nav>
    </div>
</div>
