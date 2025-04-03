/**
 * Simple Direct Web3 Wallet Connection Module
 * Handles wallet connection functionality using direct provider connection
 * Supports both desktop and mobile wallets and multiple networks
 */

// Global variables
let web3;
let provider;
let selectedAccount = null;
let currentChainId = null;
let currentNetworkData = null;

// Supported networks configuration
const NETWORKS = {
    // Mainnets
    1: {
        name: "Ethereum Mainnet",
        shortName: "Ethereum",
        nativeCurrency: { name: "Ether", symbol: "ETH", decimals: 18 },
        rpcUrl: "https://mainnet.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1",
        blockExplorerUrl: "https://etherscan.io",
        iconClass: "ethereum",
        color: "#627EEA"
    },
    56: {
        name: "Binance Smart Chain",
        shortName: "BSC",
        nativeCurrency: { name: "BNB", symbol: "BNB", decimals: 18 },
        rpcUrl: "https://bsc-dataseed.binance.org/",
        blockExplorerUrl: "https://bscscan.com",
        iconClass: "binance",
        color: "#F3BA2F"
    },
    137: {
        name: "Polygon Mainnet",
        shortName: "Polygon",
        nativeCurrency: { name: "MATIC", symbol: "MATIC", decimals: 18 },
        rpcUrl: "https://polygon-rpc.com",
        blockExplorerUrl: "https://polygonscan.com",
        iconClass: "polygon",
        color: "#8247E5"
    },
    42161: {
        name: "Arbitrum One",
        shortName: "Arbitrum",
        nativeCurrency: { name: "Ether", symbol: "ETH", decimals: 18 },
        rpcUrl: "https://arb1.arbitrum.io/rpc",
        blockExplorerUrl: "https://arbiscan.io",
        iconClass: "arbitrum",
        color: "#28A0F0"
    },
    10: {
        name: "Optimism",
        shortName: "Optimism",
        nativeCurrency: { name: "Ether", symbol: "ETH", decimals: 18 },
        rpcUrl: "https://mainnet.optimism.io",
        blockExplorerUrl: "https://optimistic.etherscan.io",
        iconClass: "optimism",
        color: "#FF0420"
    },
    
    // Testnets
    5: {
        name: "Goerli Testnet",
        shortName: "Goerli",
        testnet: true,
        nativeCurrency: { name: "Goerli Ether", symbol: "GoerliETH", decimals: 18 },
        rpcUrl: "https://goerli.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1",
        blockExplorerUrl: "https://goerli.etherscan.io",
        iconClass: "ethereum",
        color: "#3099f2"
    },
    80001: {
        name: "Mumbai Testnet",
        shortName: "Mumbai",
        testnet: true,
        nativeCurrency: { name: "Testnet MATIC", symbol: "tMATIC", decimals: 18 },
        rpcUrl: "https://rpc-mumbai.maticvigil.com",
        blockExplorerUrl: "https://mumbai.polygonscan.com",
        iconClass: "polygon",
        color: "#8247E5"
    },
    97: {
        name: "BSC Testnet",
        shortName: "BSC Testnet",
        testnet: true,
        nativeCurrency: { name: "Testnet BNB", symbol: "tBNB", decimals: 18 },
        rpcUrl: "https://data-seed-prebsc-1-s1.binance.org:8545/",
        blockExplorerUrl: "https://testnet.bscscan.com",
        iconClass: "binance",
        color: "#F3BA2F"
    }
};

// Default network (Goerli Testnet for testing with test tokens)
const DEFAULT_CHAIN_ID = 5; // Goerli Testnet

// Initialize wallet functionality
function initWalletConnection() {
    console.log("Initializing wallet connection...");
    
    // Set up event listeners
    setupEventListeners();
    
    // Check for existing connection
    checkConnection();
    
    // Set up dropdown toggle functionality
    setupDropdownToggle();
}

// Set up event listeners for wallet buttons
function setupEventListeners() {
    // Connect wallet button (can be multiple on the page)
    document.querySelectorAll('.connect-wallet-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (selectedAccount) {
                // If already connected, toggle wallet dropdown
                const walletDisplay = document.getElementById('walletDisplay');
                const dropdown = document.getElementById('walletDropdown');
                
                if (dropdown && walletDisplay) {
                    dropdown.classList.toggle('hidden');
                }
            } else {
                // Try to connect
                try {
                    await connectWallet();
                } catch (error) {
                    console.error("Error connecting wallet:", error);
                    showToast('Failed to connect wallet: ' + (error.message || "Unknown error"), 'error');
                }
            }
        });
    });
    
    // Disconnect button
    const disconnectBtn = document.getElementById('disconnectWalletBtn');
    if (disconnectBtn) {
        disconnectBtn.addEventListener('click', disconnectWallet);
    }
    
    // Copy address button
    const copyBtn = document.getElementById('copyAddressBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', copyAddressToClipboard);
    }
    
    // Modal close buttons
    const closeModalBtn = document.getElementById('closeModalBtn');
    const continueBtn = document.getElementById('continueBtn');
    const retryBtn = document.getElementById('retryBtn');
    const closeErrorBtn = document.getElementById('closeErrorBtn');
    const modalOverlay = document.getElementById('walletModalOverlay');
    
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideConnectionModal);
    if (continueBtn) continueBtn.addEventListener('click', hideConnectionModal);
    if (retryBtn) retryBtn.addEventListener('click', connectWallet);
    if (closeErrorBtn) closeErrorBtn.addEventListener('click', hideConnectionModal);
    if (modalOverlay) modalOverlay.addEventListener('click', function(e) {
        // Only close if clicking on the actual overlay, not its children
        if (e.target === modalOverlay) {
            hideConnectionModal();
        }
    });
    
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Set up dropdown toggle functionality
function setupDropdownToggle() {
    const walletDropdownToggle = document.getElementById('walletDropdownToggle');
    const walletDropdown = document.getElementById('walletDropdown');
    
    if (walletDropdownToggle && walletDropdown) {
        walletDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            walletDropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!walletDropdownToggle.contains(e.target) && !walletDropdown.contains(e.target)) {
                walletDropdown.classList.add('hidden');
            }
        });
    }
}

// Connect to wallet using the injected provider (MetaMask, etc.)
async function connectWallet() {
    try {
        showConnectionModal('loading');
        
        // Check if ethereum provider exists (MetaMask or other injected wallet)
        if (!window.ethereum) {
            const errorMsg = "No Ethereum provider found. Please install MetaMask or use a browser with a compatible wallet extension.";
            console.error(errorMsg);
            showConnectionModal('error', errorMsg);
            return false;
        }
        
        // Store provider reference
        provider = window.ethereum;
        
        // Request accounts
        try {
            const accounts = await provider.request({ method: 'eth_requestAccounts' });
            
            if (!accounts || accounts.length === 0) {
                throw new Error("No accounts found or user rejected the connection request.");
            }
            
            selectedAccount = accounts[0];
            console.log("Connected account:", selectedAccount);
            
            // Get network/chain ID
            const chainId = await provider.request({ method: 'eth_chainId' });
            currentChainId = parseInt(chainId, 16); // Convert hex to decimal
            
            console.log("Connected to chain ID:", currentChainId);
            
            // Update network display
            updateNetworkDisplay(currentChainId);
            
            // Initialize Web3
            web3 = new Web3(provider);
            
            // Setup provider event listeners
            setupProviderEvents();
            
            // Save wallet address to backend
            await saveWalletAddress(selectedAccount);
            
            // Update UI
            updateWalletUI(selectedAccount);
            showConnectionModal('success');
            
            return true;
        } catch (error) {
            console.error("Error connecting wallet:", error);
            showConnectionModal('error', error.message || "Failed to connect wallet. Please try again.");
            return false;
        }
    } catch (error) {
        console.error("Wallet connection error:", error);
        showConnectionModal('error', error.message || "An unexpected error occurred while connecting your wallet.");
        return false;
    }
}

// Disconnect wallet
async function disconnectWallet() {
    try {
        // Reset variables
        provider = null;
        web3 = null;
        selectedAccount = null;
        currentChainId = null;
        currentNetworkData = null;
        
        // Update database
        await removeWalletAddress();
        
        // Update UI
        updateWalletUI(null);
        
        // Close dropdown if open
        const dropdown = document.getElementById('walletDropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
        
        showToast('Wallet disconnected successfully', 'success');
        return true;
    } catch (error) {
        console.error("Error disconnecting wallet:", error);
        showToast('Failed to disconnect wallet', 'error');
        return false;
    }
}

// Setup provider events
function setupProviderEvents() {
    if (provider) {
        // Account change event
        provider.on('accountsChanged', (accounts) => {
            console.log("Accounts changed:", accounts);
            
            if (accounts.length === 0) {
                // User disconnected their wallet
                disconnectWallet();
            } else {
                selectedAccount = accounts[0];
                updateWalletAddress(selectedAccount);
                updateWalletUI(selectedAccount);
            }
        });
        
        // Chain/network change event
        provider.on('chainChanged', (chainIdHex) => {
            console.log("Chain changed:", chainIdHex);
            currentChainId = parseInt(chainIdHex, 16);
            updateNetworkDisplay(currentChainId);
            // No need to reload the page
        });
        
        // Disconnect event (some wallets support this)
        provider.on('disconnect', (error) => {
            console.log("Provider disconnected:", error);
            disconnectWallet();
        });
    }
}

// Update network display based on chain ID
function updateNetworkDisplay(chainId) {
    const networkDisplay = document.getElementById('networkDisplay');
    if (!networkDisplay) return;
    
    currentNetworkData = NETWORKS[chainId] || null;
    
    if (currentNetworkData) {
        networkDisplay.innerHTML = `
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full" style="background-color: ${currentNetworkData.color}"></span>
                <span class="text-xs text-gray-300">${currentNetworkData.name}</span>
            </div>
        `;
    } else {
        networkDisplay.innerHTML = `
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-orange-500"></span>
                <span class="text-xs text-gray-300">Unknown Network (${chainId})</span>
            </div>
        `;
    }
}

// Check if user is already connected
async function checkConnection() {
    // Check with the backend if there's an active session
    try {
        const response = await fetch('/api/check_connection.php');
        const data = await response.json();
        
        if (data.success && data.connected && data.address) {
            console.log("Found active session with wallet:", data.address);
            
            // Check if browser wallet is available and connected
            if (window.ethereum) {
                try {
                    // Get accounts without prompting
                    const accounts = await window.ethereum.request({ 
                        method: 'eth_accounts' // Use eth_accounts instead of eth_requestAccounts to avoid prompting
                    });
                    
                    if (accounts && accounts.length > 0) {
                        // User has wallet connected in browser
                        provider = window.ethereum;
                        selectedAccount = accounts[0];
                        
                        // If the account in the session is different from the one in the wallet,
                        // update the session with the current wallet account
                        if (selectedAccount.toLowerCase() !== data.address.toLowerCase()) {
                            console.log("Wallet account changed, updating session...");
                            await saveWalletAddress(selectedAccount);
                        }
                        
                        // Initialize Web3
                        web3 = new Web3(provider);
                        
                        // Get network/chain ID
                        const chainId = await provider.request({ method: 'eth_chainId' });
                        currentChainId = parseInt(chainId, 16);
                        
                        // Setup provider events
                        setupProviderEvents();
                        
                        // Update network display
                        updateNetworkDisplay(currentChainId);
                        
                        // Update UI
                        updateWalletUI(selectedAccount);
                        console.log("Reconnected to wallet:", selectedAccount);
                        
                        return true;
                    } else {
                        // Wallet is available but no accounts are connected
                        // We still have a server-side session, so try to reconnect
                        console.log("Session exists but wallet is disconnected. Attempting to reconnect...");
                        
                        try {
                            // Try to reconnect wallet
                            const reconnectedAccounts = await window.ethereum.request({ 
                                method: 'eth_requestAccounts'
                            });
                            
                            if (reconnectedAccounts && reconnectedAccounts.length > 0) {
                                // Successfully reconnected
                                provider = window.ethereum;
                                selectedAccount = reconnectedAccounts[0];
                                
                                // Initialize Web3
                                web3 = new Web3(provider);
                                
                                // Get network/chain ID
                                const chainId = await provider.request({ method: 'eth_chainId' });
                                currentChainId = parseInt(chainId, 16);
                                
                                // Setup provider events
                                setupProviderEvents();
                                
                                // Update network display
                                updateNetworkDisplay(currentChainId);
                                
                                // Update UI
                                updateWalletUI(selectedAccount);
                                console.log("Auto-reconnected to wallet:", selectedAccount);
                                
                                return true;
                            }
                        } catch (reconnectError) {
                            console.log("Auto-reconnect failed:", reconnectError);
                            // User rejected the reconnection, clear the session
                            await removeWalletAddress();
                        }
                    }
                } catch (error) {
                    console.error("Error checking wallet connection:", error);
                }
            }
            
            // If we can't connect to the wallet directly but have a session,
            // still update the UI so the user can reconnect easily
            updateWalletUI(data.address);
            selectedAccount = data.address;
            showToast(`Welcome back! Your wallet ${truncateAddress(data.address)} is connected.`, 'info');
        }
    } catch (error) {
        console.error("Error checking connection status:", error);
    }
    
    return false;
}

// Save wallet address to backend
async function saveWalletAddress(address) {
    try {
        const response = await fetch('/api/connect.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ address: address }),
        });
        
        const data = await response.json();
        
        if (!data.success) {
            console.error("Error saving wallet address:", data.message);
        }
        
        return data.success;
    } catch (error) {
        console.error("Error saving wallet address:", error);
        return false;
    }
}

// Update wallet address in backend
async function updateWalletAddress(address) {
    // Similar to saveWalletAddress but for updating
    return await saveWalletAddress(address);
}

// Remove wallet address from backend
async function removeWalletAddress() {
    try {
        const response = await fetch('/api/disconnect.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (!data.success) {
            console.error("Error removing wallet address:", data.message);
        }
        
        return data.success;
    } catch (error) {
        console.error("Error removing wallet address:", error);
        return false;
    }
}

// Update UI based on wallet connection status
function updateWalletUI(address) {
    const connectBtns = document.querySelectorAll('.connect-wallet-btn');
    const walletDisplay = document.getElementById('walletDisplay');
    const walletDropdown = document.getElementById('walletDropdown');
    
    if (address) {
        // Update all connect buttons to say "Connected"
        connectBtns.forEach(btn => {
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Connected';
            btn.classList.add('connected');
        });
        
        // Show wallet display in header
        if (walletDisplay) {
            walletDisplay.classList.remove('hidden');
            document.getElementById('walletAddressText').textContent = truncateAddress(address);
        }
        
        // Enable wallet dropdown
        if (walletDropdown) {
            walletDropdown.classList.remove('hidden');
        }
    } else {
        // Reset connect buttons
        connectBtns.forEach(btn => {
            btn.innerHTML = '<i class="fas fa-wallet"></i> Connect Wallet';
            btn.classList.remove('connected');
        });
        
        // Hide wallet display in header
        if (walletDisplay) {
            walletDisplay.classList.add('hidden');
        }
        
        // Disable wallet dropdown
        if (walletDropdown) {
            walletDropdown.classList.add('hidden');
        }
    }
    
    // Update connected address in modal
    const connectedAddressEl = document.getElementById('connectedAddress');
    if (connectedAddressEl && address) {
        connectedAddressEl.textContent = truncateAddress(address);
    }
}

// Show wallet connection modal
function showConnectionModal(state, errorMessage = '') {
    const modal = document.getElementById('walletConnectionStatus');
    const loadingState = document.getElementById('connectionLoading');
    const successState = document.getElementById('connectionSuccess');
    const errorState = document.getElementById('connectionError');
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Hide all states first
    loadingState.classList.add('hidden');
    successState.classList.add('hidden');
    errorState.classList.add('hidden');
    
    // Show appropriate state
    if (state === 'loading') {
        loadingState.classList.remove('hidden');
    } else if (state === 'success') {
        successState.classList.remove('hidden');
    } else if (state === 'error') {
        errorState.classList.remove('hidden');
        document.getElementById('errorMessage').textContent = errorMessage || 'Unable to connect to wallet. Please try again.';
    }
    
    // Add animation class
    modal.classList.add('animate__animated', 'animate__fadeIn');
}

// Hide wallet connection modal
function hideConnectionModal() {
    const modal = document.getElementById('walletConnectionStatus');
    modal.classList.add('animate__fadeOut');
    
    // Remove the modal after animation completes
    setTimeout(() => {
        modal.classList.remove('animate__fadeOut');
        modal.classList.add('hidden');
    }, 500);
}

// Helper function to truncate Ethereum address
function truncateAddress(address) {
    if (!address) return '';
    return address.substring(0, 6) + '...' + address.substring(address.length - 4);
}

// Copy address to clipboard
function copyAddressToClipboard() {
    const address = selectedAccount;
    if (!address) return;
    
    navigator.clipboard.writeText(address)
        .then(() => {
            showToast('Address copied to clipboard!', 'success');
        })
        .catch(err => {
            console.error('Failed to copy address:', err);
            showToast('Failed to copy address', 'error');
        });
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col items-end space-y-2';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `animate__animated animate__fadeInUp mb-3 p-4 rounded-lg text-white font-medium flex items-center shadow-lg max-w-md`;
    
    // Set background color based on type
    if (type === 'success') {
        toast.classList.add('bg-green-600');
        toast.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2 flex-shrink-0">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg> ${message}`;
    } else if (type === 'error') {
        toast.classList.add('bg-red-600');
        toast.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2 flex-shrink-0">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg> ${message}`;
    } else {
        toast.classList.add('bg-indigo-600');
        toast.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2 flex-shrink-0">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
        </svg> ${message}`;
    }
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ml-auto text-white/80 hover:text-white';
    closeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>';
    closeBtn.onclick = () => {
        removeToast(toast);
    };
    toast.appendChild(closeBtn);
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeToast(toast);
    }, 5000);
}

// Remove toast notification
function removeToast(toast) {
    toast.classList.remove('animate__fadeInUp');
    toast.classList.add('animate__fadeOutDown');
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 500);
}

// Add wallet connection modal to the page if it doesn't exist
function ensureModalExists() {
    if (!document.getElementById('walletConnectionStatus')) {
        const modalHTML = `
        <div id="walletConnectionStatus" class="fixed inset-0 flex items-center justify-center z-50 hidden">
            <div id="walletModalOverlay" class="absolute inset-0 bg-black bg-opacity-75 backdrop-blur-sm"></div>
            <div class="relative bg-gray-900 rounded-xl max-w-md w-full mx-4 shadow-2xl border border-gray-700 overflow-hidden">
                <!-- Loading State -->
                <div id="connectionLoading" class="p-6">
                    <div class="flex flex-col items-center text-center py-6">
                        <div class="w-16 h-16 mb-6 relative">
                            <div class="absolute inset-0 rounded-full border-4 border-indigo-500/30 animate-ping"></div>
                            <div class="w-16 h-16 rounded-full border-4 border-t-indigo-600 border-indigo-500/30 animate-spin"></div>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Connecting Wallet</h3>
                        <p class="text-gray-400">Please confirm the connection in your wallet...</p>
                    </div>
                </div>
                
                <!-- Success State -->
                <div id="connectionSuccess" class="p-6 hidden">
                    <div class="flex flex-col items-center text-center py-6">
                        <div class="w-16 h-16 mb-6 bg-green-500/20 rounded-full flex items-center justify-center text-green-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Wallet Connected!</h3>
                        <p class="text-gray-400 mb-4">Your wallet has been successfully connected.</p>
                        <div class="bg-gray-800 rounded-lg p-3 flex items-center justify-between w-full">
                            <span id="connectedAddress" class="font-mono text-indigo-400"></span>
                            <span class="flex items-center gap-1.5 text-xs bg-green-500/20 text-green-500 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Connected
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-end bg-gray-800/50 p-4 -mx-6 -mb-6 border-t border-gray-700/50">
                        <button id="continueBtn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white transition">Continue</button>
                    </div>
                </div>
                
                <!-- Error State -->
                <div id="connectionError" class="p-6 hidden">
                    <div class="flex flex-col items-center text-center py-6">
                        <div class="w-16 h-16 mb-6 bg-red-500/20 rounded-full flex items-center justify-center text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Connection Error</h3>
                        <p id="errorMessage" class="text-gray-400 mb-4">Unable to connect to wallet. Please try again.</p>
                    </div>
                    <div class="flex justify-end gap-3 bg-gray-800/50 p-4 -mx-6 -mb-6 border-t border-gray-700/50">
                        <button id="closeErrorBtn" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white transition">Close</button>
                        <button id="retryBtn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white transition">Try Again</button>
                    </div>
                </div>
            </div>
        </div>`;
        
        // Add modal to the body
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHTML;
        document.body.appendChild(modalContainer.firstElementChild);
    }
}

// Initialize wallet features on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded, initializing wallet connection...");
    
    // Ensure modal exists
    ensureModalExists();
    
    // Initialize wallet connection
    initWalletConnection();
});

// Backup initialization if DOMContentLoaded already happened
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    console.log("Document already loaded, initializing wallet connection directly...");
    
    // Ensure modal exists
    ensureModalExists();
    
    // Initialize wallet connection
    initWalletConnection();
}
