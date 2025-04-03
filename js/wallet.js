/**
 * Web3Modal Wallet Connection Module
 * Handles wallet connection functionality using Web3Modal
 * Enhanced to support both desktop and mobile wallets
 * 
 * This module has been simplified to increase compatibility
 * across different environments and wallets
 */

// Global variables
let web3;
let provider;
let web3Modal;
let selectedAccount = null;

// Initialize Web3Modal with a more simplified approach
function initWeb3Modal() {
    try {
        console.log("Initializing Web3Modal...");
        
        // Check if Web3Modal is available
        if (typeof Web3Modal !== 'function' && typeof window.Web3Modal !== 'function') {
            console.warn("Web3Modal not available yet, will retry in 800ms");
            setTimeout(initWeb3Modal, 800);
            return;
        }
        
        // Get the proper Web3Modal constructor
        const Web3ModalConstructor = typeof Web3Modal === 'function' ? Web3Modal : window.Web3Modal;
        
        // Set up WalletConnect provider if available
        const providerOptions = {};
        
        // Check for WalletConnect provider availability
        if (typeof WalletConnectProvider === 'function' || typeof window.WalletConnectProvider === 'function') {
            const WCProvider = typeof WalletConnectProvider === 'function' ? 
                               WalletConnectProvider : window.WalletConnectProvider;
            
            console.log("WalletConnect provider found, adding to options");
            
            // Add WalletConnect
            providerOptions.walletconnect = {
                package: WCProvider,
                options: {
                    infuraId: "27e484dcd9e3efcfd25a83a78777cdf1",
                    rpc: {
                        1: "https://mainnet.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1",
                        56: "https://bsc-dataseed.binance.org/",
                        137: "https://polygon-rpc.com/"
                    },
                    qrcodeModalOptions: {
                        mobileLinks: [
                            "metamask",
                            "trust",
                            "coinbase",
                            "rainbow",
                            "zerion",
                            "okxwallet"
                        ]
                    }
                }
            };
        } else {
            console.warn("WalletConnect provider not available");
        }
        
        // Create Web3Modal instance with simplified configuration
        web3Modal = new Web3ModalConstructor({
            cacheProvider: true,
            providerOptions: providerOptions,
            disableInjectedProvider: false,
            theme: {
                background: "rgb(15, 23, 42)",
                main: "rgb(255, 255, 255)",
                secondary: "rgb(148, 163, 184)",
                border: "rgba(59, 130, 246, 0.3)",
                hover: "rgb(30, 41, 59)"
            }
        });
        
        console.log("Web3Modal initialized successfully");
        
        // Setup listeners and check for existing connection
        setupConnectButtonListeners();
        checkConnection();
        
    } catch (error) {
        console.error("Error initializing Web3Modal:", error);
        // Set up fallback listeners if initialization fails
        addFallbackListeners();
    }
}

// Setup connect button event listeners
function setupConnectButtonListeners() {
    // Connect wallet button click handler
    document.querySelectorAll('.connect-wallet-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (selectedAccount) {
                // If already connected, show the dropdown menu
                const dropdown = document.getElementById('walletDropdown');
                if (dropdown) {
                    dropdown.classList.toggle('hidden');
                }
            } else {
                // If not connected, try to connect
                try {
                    await connectWallet();
                } catch (error) {
                    console.error("Error connecting wallet:", error);
                    showToast('Failed to connect wallet. Please try again.', 'error');
                }
            }
        });
    });
    
    // Disconnect button click handler
    const disconnectBtn = document.getElementById('disconnectWalletBtn');
    if (disconnectBtn) {
        disconnectBtn.addEventListener('click', async function() {
            try {
                await disconnectWallet();
            } catch (error) {
                console.error("Error disconnecting wallet:", error);
                showToast('Failed to disconnect wallet. Please try again.', 'error');
            }
        });
    }
    
    // Copy address button
    const copyBtn = document.getElementById('copyAddressBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', copyAddressToClipboard);
    }
    
    // Setup modal close buttons
    const closeModalBtn = document.getElementById('closeModalBtn');
    const continueBtn = document.getElementById('continueBtn');
    const retryBtn = document.getElementById('retryBtn');
    const closeErrorBtn = document.getElementById('closeErrorBtn');
    const modalOverlay = document.getElementById('walletModalOverlay');
    
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideConnectionModal);
    if (continueBtn) continueBtn.addEventListener('click', hideConnectionModal);
    if (retryBtn) retryBtn.addEventListener('click', connectWallet);
    if (closeErrorBtn) closeErrorBtn.addEventListener('click', hideConnectionModal);
    if (modalOverlay) modalOverlay.addEventListener('click', hideConnectionModal);
}

// Add fallback listeners when Web3Modal fails to initialize
function addFallbackListeners() {
    document.querySelectorAll('.connect-wallet-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            showToast('Wallet connection is not available. Please make sure you have MetaMask or a mobile wallet like Trust Wallet, Coinbase Wallet, or MetaMask Mobile.', 'error');
        });
    });
}

// Connect to wallet
async function connectWallet() {
    try {
        showConnectionModal('loading');
        
        // Check if web3Modal is initialized
        if (!web3Modal) {
            throw new Error("Web3Modal not initialized. Please make sure you have a compatible wallet extension installed.");
        }
        
        // Connect to provider
        try {
            provider = await web3Modal.connect();
        } catch (connectError) {
            console.error("Connection error:", connectError);
            let errorMessage = "Could not connect to wallet. ";
            
            if (connectError.message) {
                errorMessage += connectError.message;
            } else {
                errorMessage += "Please make sure you have a compatible wallet like MetaMask, Trust Wallet, Coinbase Wallet, or OKX Wallet installed and activated.";
            }
            
            showConnectionModal('error', errorMessage);
            return false;
        }
        
        // Initialize Web3 with provider
        try {
            web3 = new Web3(provider);
        } catch (web3Error) {
            console.error("Web3 initialization error:", web3Error);
            showConnectionModal('error', "Failed to initialize Web3. Please refresh and try again.");
            return false;
        }
        
        // Get accounts
        try {
            const accounts = await web3.eth.getAccounts();
            if (!accounts || accounts.length === 0) {
                throw new Error("No accounts found. Please make sure your wallet is unlocked and permissions are granted.");
            }
            selectedAccount = accounts[0];
        } catch (accountsError) {
            console.error("Error getting accounts:", accountsError);
            showConnectionModal('error', accountsError.message || "Could not get your wallet accounts. Please check your wallet permissions.");
            return false;
        }
        
        // Setup provider event listeners
        setupProviderEvents();
        
        // Save account to database
        try {
            await saveWalletAddress(selectedAccount);
        } catch (saveError) {
            console.error("Error saving wallet address:", saveError);
            // Continue anyway as this is not critical
        }
        
        // Update UI
        updateWalletUI(selectedAccount);
        showConnectionModal('success');
        
        return true;
    } catch (error) {
        console.error("Could not connect to wallet:", error);
        showConnectionModal('error', error.message || "An unexpected error occurred while connecting your wallet.");
        return false;
    }
}

// Disconnect wallet
async function disconnectWallet() {
    try {
        if (provider && provider.close) {
            await provider.close();
        }
        
        // Clear cached provider
        await web3Modal.clearCachedProvider();
        
        // Reset variables
        provider = null;
        web3 = null;
        selectedAccount = null;
        
        // Update database
        await removeWalletAddress();
        
        // Update UI
        updateWalletUI(null);
        
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
    if (provider && provider.on) {
        // Subscribe to accounts change
        provider.on("accountsChanged", (accounts) => {
            if (accounts.length === 0) {
                // User disconnected their wallet
                disconnectWallet();
            } else {
                selectedAccount = accounts[0];
                updateWalletAddress(selectedAccount);
                updateWalletUI(selectedAccount);
            }
        });

        // Subscribe to chainId change
        provider.on("chainChanged", (chainId) => {
            console.log("Chain changed:", chainId);
            // Reload the page to avoid any errors
            window.location.reload();
        });

        // Subscribe to provider disconnection
        provider.on("disconnect", (error) => {
            console.log("Provider disconnected:", error);
            disconnectWallet();
        });
    }
}

// Check if user is already connected
async function checkConnection() {
    // First try to reconnect to cached provider if available
    if (web3Modal && web3Modal.cachedProvider) {
        try {
            console.log("Found cached provider, attempting to reconnect...");
            await connectWallet();
        } catch (error) {
            console.error("Error reconnecting to cached provider:", error);
        }
    }
    
    // Also check the backend session (for cases where the browser was closed)
    try {
        const response = await fetch('api/check_connection.php');
        const data = await response.json();
        
        if (data.success) {
            if (data.connected && data.address && !selectedAccount) {
                console.log("Session has a connected wallet, updating UI...");
                showToast(`Welcome back! Your wallet ${truncateAddress(data.address)} is connected.`, 'info');
                updateWalletUI(data.address);
                selectedAccount = data.address;
            }
        } else if (data.error) {
            console.error("Server error checking connection:", data.error);
        }
    } catch (error) {
        console.error("Network error checking wallet connection status:", error);
    }
}

// Save wallet address to backend
async function saveWalletAddress(address) {
    try {
        const response = await fetch('api/connect.php', {
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
        const response = await fetch('api/disconnect.php', {
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
    const toastContainer = document.getElementById('toastContainer');
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `animate__animated animate__fadeInUp mb-3 p-4 rounded-lg text-white font-medium flex items-center shadow-lg`;
    
    // Set background color based on type
    if (type === 'success') {
        toast.classList.add('bg-green-600');
        toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i> ${message}`;
    } else if (type === 'error') {
        toast.classList.add('bg-red-600');
        toast.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i> ${message}`;
    } else {
        toast.classList.add('bg-blue-600');
        toast.innerHTML = `<i class="fas fa-info-circle mr-2"></i> ${message}`;
    }
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ml-auto text-white/80 hover:text-white';
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
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

// Simple initialization on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded, initializing Web3Modal...");
    setTimeout(initWeb3Modal, 100); // Small delay to ensure libraries are loaded
});

// Backup initialization if DOMContentLoaded already happened
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    console.log("Document already loaded, initializing Web3Modal directly...");
    setTimeout(initWeb3Modal, 100);
}
