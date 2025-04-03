/**
 * Web3Modal Wallet Connection Module
 * Handles wallet connection functionality using Web3Modal
 */

// Global variables
let web3;
let provider;
let web3Modal;
let selectedAccount = null;

// Initialize Web3Modal
function initWeb3Modal() {
    // Make sure required libraries are loaded
    if (typeof Web3Modal === 'undefined') {
        console.error("Web3Modal library not loaded");
        return;
    }
    
    if (typeof WalletConnectProvider === 'undefined') {
        console.error("WalletConnectProvider library not loaded");
        return;
    }
    
    // Define providers
    const providerOptions = {
        walletconnect: {
            package: WalletConnectProvider,
            options: {
                infuraId: "27e484dcd9e3efcfd25a83a78777cdf1", // Replace with your Infura ID or use env variable
            }
        },
        // Add more providers as needed
    };

    try {
        // Initialize Web3Modal
        web3Modal = new Web3Modal({
            cacheProvider: true, // optional
            providerOptions, // required
            disableInjectedProvider: false, // optional. For MetaMask / Brave / Opera.
            theme: {
                background: "rgb(15, 23, 42)",
                main: "rgb(255, 255, 255)",
                secondary: "rgb(148, 163, 184)",
                border: "rgba(59, 130, 246, 0.3)",
                hover: "rgb(30, 41, 59)"
            }
        });
        
        console.log("Web3Modal initialized successfully");
        
        // Check if user was previously connected
        checkConnection();
    } catch (error) {
        console.error("Failed to initialize Web3Modal:", error);
    }
}

// Connect to wallet
async function connectWallet() {
    try {
        showConnectionModal('loading');
        
        provider = await web3Modal.connect();
        web3 = new Web3(provider);
        
        const accounts = await web3.eth.getAccounts();
        selectedAccount = accounts[0];
        
        // Setup provider event listeners
        setupProviderEvents();
        
        // Save account to database
        await saveWalletAddress(selectedAccount);
        
        // Update UI
        updateWalletUI(selectedAccount);
        showConnectionModal('success');
        
        return true;
    } catch (error) {
        console.error("Could not connect to wallet:", error);
        showConnectionModal('error', error.message);
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
    // Check if provider is cached
    if (web3Modal.cachedProvider) {
        try {
            await connectWallet();
        } catch (error) {
            console.error("Error reconnecting to cached provider:", error);
        }
    }
    
    // Also check the backend session
    try {
        const response = await fetch('api/check_connection.php');
        const data = await response.json();
        
        if (data.connected && data.address && !selectedAccount) {
            showToast(`Welcome back! Your wallet ${truncateAddress(data.address)} is connected.`, 'info');
            updateWalletUI(data.address);
        }
    } catch (error) {
        console.error("Error checking wallet connection status:", error);
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

// Initialize module on page load
document.addEventListener('DOMContentLoaded', initWeb3Modal);
