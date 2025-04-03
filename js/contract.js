/**
 * Contract interaction functionality
 * Handles interactions with the CryptoFund smart contract
 */

// Global contract instance
let cryptoFundContract = null;

// Load contract data
async function loadContractData() {
    try {
        console.log('Fetching contract-data.json...');
        const response = await fetch('/contract-data.json');
        
        if (!response.ok) {
            console.error('Failed to load contract data:', {
                status: response.status,
                statusText: response.statusText
            });
            throw new Error(`Failed to load contract data: ${response.status} ${response.statusText}`);
        }
        
        console.log('Contract data response received successfully');
        const data = await response.json();
        console.log('Contract data parsed successfully');
        
        return data;
    } catch (error) {
        console.error('Error loading contract data:', error);
        console.log('Contract data error details:', {
            message: error.message,
            stack: error.stack
        });
        
        // Show a user-friendly message
        if (typeof window.showToast === 'function') {
            window.showToast('Error loading contract data. Please check your connection and try again.', 'error');
        }
        
        return null;
    }
}

// Initialize contract
async function initializeContract() {
    // Add a guard to prevent recursive initialization
    if (window._isInitializingContract) {
        console.warn('Contract initialization already in progress, skipping duplicate call');
        return false;
    }
    
    window._isInitializingContract = true;
    console.log('Initializing contract with web3 provider...');
    
    try {
        // Check if Web3 is available
        if (!window.ethereum) {
            console.error('Web3 provider not available. Please install MetaMask or a compatible wallet.');
            showToast('Please install MetaMask or a compatible wallet to use all features.', 'error');
            window._isInitializingContract = false;
            return false;
        }
        
        // Check if connected
        if (!window.ethereum.isConnected()) {
            console.error('Web3 provider is not connected');
            showToast('Your wallet is not connected. Please connect your wallet to continue.', 'error');
            window._isInitializingContract = false;
            return false;
        }
        
        // Get the contract data
        console.log('Loading contract data from JSON file...');
        const contractData = await loadContractData();
        
        // Log the data to verify it loaded correctly
        console.log('Contract data loaded:', {
            address: contractData?.address,
            networkId: contractData?.networkId,
            networkName: contractData?.networkName,
            hasAbi: contractData?.abi ? 'Yes (length: ' + contractData.abi.length + ')' : 'No'
        });
        
        if (!contractData) {
            console.error('Failed to load contract data, aborting initialization');
            window._isInitializingContract = false;
            return false;
        }
        
        // Get the provider
        const provider = new ethers.providers.Web3Provider(window.ethereum);
        
        // Get the current network
        const network = await provider.getNetwork();
        console.log('Current network:', network);
        
        // Check if contract is available on the current network
        const currentNetworkId = network.chainId;
        
        // Store the current chain ID in the window global for easy access
        window.currentChainId = currentNetworkId;
        
        // First check if we have a contract address for this network
        if (contractData.contractPerNetwork && contractData.contractPerNetwork[currentNetworkId]) {
            console.log(`Contract found on current network: ${contractData.contractPerNetwork[currentNetworkId].networkName} (chainId: ${currentNetworkId})`);
            // We can use this network's contract
            contractData.address = contractData.contractPerNetwork[currentNetworkId].address;
        } else if (contractData.networkId && network.chainId !== contractData.networkId) {
            console.warn(`Contract is not available on current network chainId: ${network.chainId}, need to switch to ${contractData.networkName} (chainId: ${contractData.networkId})`);
            
            // Remember the target network for future use
            window._targetNetworkId = contractData.networkId;
            window._targetNetworkName = contractData.networkName;
            
            // Display message to user
            showToast(`Please switch to ${contractData.networkName} to interact with this contract. Use the network switcher in your wallet or click the network switch button.`, 'info');
            
            // Add a small UI button to switch networks if not already present
            const switchBtn = document.getElementById('js-network-switch-btn');
            if (!switchBtn) {
                const btnContainer = document.createElement('div');
                btnContainer.className = 'fixed bottom-24 right-4 z-40';
                btnContainer.innerHTML = `
                    <button id="js-network-switch-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-lg transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                        Switch to ${contractData.networkName}
                    </button>
                `;
                document.body.appendChild(btnContainer);
                
                // Add event listener to the button
                document.getElementById('js-network-switch-btn').addEventListener('click', async () => {
                    try {
                        if (window.switchNetwork && window._targetNetworkId) {
                            await window.switchNetwork(window._targetNetworkId);
                            setTimeout(async () => {
                                window._isInitializingContract = false;
                                await initializeContract();
                            }, 1000);
                        }
                    } catch (error) {
                        console.error('Error switching network from button:', error);
                    }
                });
            }
            
            window._isInitializingContract = false;
            return false;
        }
        
        // Get the signer (account)
        const signer = provider.getSigner();
        
        // Create the contract instance
        cryptoFundContract = new ethers.Contract(
            contractData.address,
            contractData.abi,
            signer
        );
        
        console.log('Contract initialized on network:', contractData.networkName, cryptoFundContract.address);
        
        // Show testnet info if applicable
        if (contractData.networkId === 11155111) {
            showToast('You are connected to Sepolia Testnet. Use test ETH for transactions.', 'info');
        } else if (contractData.networkId === 5) {
            showToast('You are connected to Goerli Testnet. Use test ETH for transactions.', 'info');
        } else if (contractData.networkId === 80001) {
            showToast('You are connected to Mumbai Testnet. Use test MATIC for transactions.', 'info');
        } else if (contractData.networkName && contractData.networkName.toLowerCase().includes('testnet')) {
            showToast(`You are connected to ${contractData.networkName}. Use test tokens for transactions.`, 'info');
        }
        
        window._isInitializingContract = false;
        return true;
    } catch (error) {
        console.error('Error initializing contract:', error);
        console.log('Error details:', {
            message: error.message,
            code: error.code,
            stack: error.stack,
            data: error.data
        });
        showToast('Error initializing contract. Check console for details.', 'error');
        window._isInitializingContract = false;
        return false;
    }
}

// Create campaign
async function createCampaign(title, description, goal, durationInDays) {
    if (!cryptoFundContract) {
        if (!(await initializeContract())) {
            throw new Error('Contract not initialized');
        }
    }
    
    try {
        // Convert goal to wei (smallest unit)
        const goalInWei = ethers.utils.parseEther(goal.toString());
        
        // Send the transaction
        const tx = await cryptoFundContract.createCampaign(
            title,
            description,
            goalInWei,
            durationInDays
        );
        
        // Wait for transaction to be mined
        const receipt = await tx.wait();
        
        // Find the CampaignCreated event
        const event = receipt.events.find(e => e.event === 'CampaignCreated');
        if (!event) {
            throw new Error('Campaign creation event not found');
        }
        
        // Get the campaign ID from the event
        const campaignId = event.args.id.toNumber();
        
        return {
            success: true,
            campaignId,
            transactionHash: receipt.transactionHash
        };
    } catch (error) {
        console.error('Error creating campaign:', error);
        throw error;
    }
}

// Contribute to campaign (no staking)
async function contribute(campaignId, amount) {
    try {
        // First check if contract is initialized or needs to be initialized
        if (!cryptoFundContract) {
            // Initialize contract but don't recursively call contribute again
            const initialized = await initializeContract();
            if (!initialized) {
                showToast('Unable to initialize the contract. Please check your wallet connection.', 'error');
                throw new Error('Contract not initialized');
            }
        }
        
        // Handle network switching if needed
        const chainIdElement = document.getElementById('chainId');
        if (chainIdElement && chainIdElement.value) {
            const requiredChainId = parseInt(chainIdElement.value);
            
            // Check if we're on the correct network using window.currentChainId
            if (window.currentChainId !== requiredChainId) {
                const networkNameElement = document.querySelector('[data-network-name]');
                const networkName = networkNameElement ? networkNameElement.getAttribute('data-network-name') : 'required';
                
                showToast(`Switching to ${networkName} network...`, 'info');
                try {
                    // Use window.switchNetwork which should be the global reference to the wallet.js function
                    await window.switchNetwork(requiredChainId);
                    
                    // After network is switched, re-initialize contract
                    const reInitialized = await initializeContract();
                    if (!reInitialized) {
                        showToast('Unable to connect to the new network. Please try manually switching networks.', 'error');
                        throw new Error('Contract re-initialization failed after network switch');
                    }
                    
                    // Add a delay to allow the network to stabilize before proceeding
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    
                } catch (switchError) {
                    console.error('Network switching error:', switchError);
                    showToast('Please switch networks manually and try again', 'error');
                    throw new Error(`Please switch to the required network to contribute to this campaign`);
                }
            }
        }
        
        // Safety check: at this point contract should be initialized
        if (!cryptoFundContract) {
            showToast('Contract not properly initialized. Please refresh and try again.', 'error');
            throw new Error('Contract not properly initialized after network switch');
        }
        
        // Convert amount to wei
        const amountInWei = ethers.utils.parseEther(amount.toString());
        
        // Send the transaction
        console.log(`Sending contribution of ${amount} ETH to campaign ${campaignId}`);
        const tx = await cryptoFundContract.contribute(campaignId, {
            value: amountInWei
        });
        
        showToast('Transaction submitted! Waiting for confirmation...', 'info');
        
        // Wait for transaction to be mined
        const receipt = await tx.wait();
        console.log('Transaction confirmed:', receipt);
        
        return {
            success: true,
            transactionHash: receipt.transactionHash
        };
    } catch (error) {
        console.error('Error contributing to campaign:', error);
        
        // Better error handling for user
        if (error.code === 'ACTION_REJECTED') {
            showToast('Transaction was rejected. You denied the transaction request.', 'error');
        } else if (error.message && error.message.includes('call stack size')) {
            showToast('Error: Maximum call stack size exceeded. Please refresh the page and try again.', 'error');
        } else {
            showToast('Transaction failed: ' + (error.message || 'Unknown error'), 'error');
        }
        
        throw error;
    }
}

// Contribute with staking
async function contributeWithStaking(campaignId, amount, stakingPeriodInDays) {
    try {
        // First check if contract is initialized or needs to be initialized
        if (!cryptoFundContract) {
            // Initialize contract but don't recursively call stake again
            const initialized = await initializeContract();
            if (!initialized) {
                showToast('Unable to initialize the contract. Please check your wallet connection.', 'error');
                throw new Error('Contract not initialized');
            }
        }
        
        // Handle network switching if needed
        const chainIdElement = document.getElementById('chainId');
        if (chainIdElement && chainIdElement.value) {
            const requiredChainId = parseInt(chainIdElement.value);
            
            // Check if we're on the correct network using window.currentChainId
            if (window.currentChainId !== requiredChainId) {
                const networkNameElement = document.querySelector('[data-network-name]');
                const networkName = networkNameElement ? networkNameElement.getAttribute('data-network-name') : 'required';
                
                showToast(`Switching to ${networkName} network...`, 'info');
                try {
                    // Use window.switchNetwork which should be the global reference to the wallet.js function
                    await window.switchNetwork(requiredChainId);
                    
                    // After network is switched, re-initialize contract
                    const reInitialized = await initializeContract();
                    if (!reInitialized) {
                        showToast('Unable to connect to the new network. Please try manually switching networks.', 'error');
                        throw new Error('Contract re-initialization failed after network switch');
                    }
                    
                    // Add a delay to allow the network to stabilize before proceeding
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    
                } catch (switchError) {
                    console.error('Network switching error:', switchError);
                    showToast('Please switch networks manually and try again', 'error');
                    throw new Error(`Please switch to the required network to stake in this campaign`);
                }
            }
        }
        
        // Safety check: at this point contract should be initialized
        if (!cryptoFundContract) {
            showToast('Contract not properly initialized. Please refresh and try again.', 'error');
            throw new Error('Contract not properly initialized after network switch');
        }
        
        // Convert amount to wei
        const amountInWei = ethers.utils.parseEther(amount.toString());
        
        // Send the transaction
        console.log(`Sending staking contribution of ${amount} ETH to campaign ${campaignId} for ${stakingPeriodInDays} days`);
        const tx = await cryptoFundContract.contributeWithStaking(
            campaignId,
            stakingPeriodInDays,
            {
                value: amountInWei
            }
        );
        
        showToast('Staking transaction submitted! Waiting for confirmation...', 'info');
        
        // Wait for transaction to be mined
        const receipt = await tx.wait();
        console.log('Staking transaction confirmed:', receipt);
        
        return {
            success: true,
            transactionHash: receipt.transactionHash
        };
    } catch (error) {
        console.error('Error staking in campaign:', error);
        
        // Better error handling for user
        if (error.code === 'ACTION_REJECTED') {
            showToast('Transaction was rejected. You denied the transaction request.', 'error');
        } else if (error.message && error.message.includes('call stack size')) {
            showToast('Error: Maximum call stack size exceeded. Please refresh the page and try again.', 'error');
        } else {
            showToast('Staking transaction failed: ' + (error.message || 'Unknown error'), 'error');
        }
        
        throw error;
    }
}

// Get campaign details
async function getCampaign(campaignId) {
    try {
        // First check if contract is initialized or needs to be initialized
        if (!cryptoFundContract) {
            const initialized = await initializeContract();
            if (!initialized) {
                console.error('Unable to initialize contract');
                throw new Error('Contract not initialized');
            }
        }
        
        // Get campaign data
        console.log(`Getting campaign details for ID: ${campaignId}`);
        const campaign = await cryptoFundContract.getCampaign(campaignId);
        
        // Format the campaign data
        return {
            id: campaign.id.toNumber(),
            owner: campaign.owner,
            title: campaign.title,
            description: campaign.description,
            goal: ethers.utils.formatEther(campaign.goal),
            currentAmount: ethers.utils.formatEther(campaign.currentAmount),
            endTime: new Date(campaign.endTime.toNumber() * 1000),
            claimed: campaign.claimed,
            canceled: campaign.canceled
        };
    } catch (error) {
        console.error('Error getting campaign:', error);
        
        // Better error handling
        if (error.message && error.message.includes('call stack size')) {
            showToast('Error: Maximum call stack size exceeded. Please refresh the page and try again.', 'error');
        }
        
        throw error;
    }
}

// Get staking details
async function getStakingContribution(campaignId, address) {
    try {
        // First check if contract is initialized or needs to be initialized
        if (!cryptoFundContract) {
            const initialized = await initializeContract();
            if (!initialized) {
                console.error('Unable to initialize contract');
                throw new Error('Contract not initialized');
            }
        }
        
        // Get staking data
        console.log(`Getting staking details for campaign ${campaignId} and address ${address}`);
        const staking = await cryptoFundContract.getStakingContribution(campaignId, address);
        
        // Format the staking data
        return {
            amount: ethers.utils.formatEther(staking.amount),
            stakingPeriod: staking.stakingPeriod.toNumber(),
            stakingEndTime: new Date(staking.stakingEndTime.toNumber() * 1000),
            reward: ethers.utils.formatEther(staking.reward),
            claimed: staking.claimed
        };
    } catch (error) {
        console.error('Error getting staking details:', error);
        
        // Better error handling
        if (error.message && error.message.includes('call stack size')) {
            showToast('Error: Maximum call stack size exceeded. Please refresh the page and try again.', 'error');
        }
        
        throw error;
    }
}

// Show toast notification - relay to wallet.js if available or create a local version
function showToast(message, type = 'info') {
    // First try to use the showToast function from wallet.js if it exists
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
        return;
    }
    
    // If wallet.js showToast isn't available, create a simpler version here
    console.log(`[${type.toUpperCase()}] ${message}`);
    
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
    toast.className = `mb-3 p-4 rounded-lg text-white font-medium flex items-center shadow-lg max-w-md transition-all duration-300 ease-in-out`;
    
    // Set background color based on type
    if (type === 'success') {
        toast.classList.add('bg-green-600');
    } else if (type === 'error') {
        toast.classList.add('bg-red-600');
    } else {
        toast.classList.add('bg-indigo-600');
    }
    
    toast.textContent = message;
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.add('opacity-100');
    }, 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
    console.log('Initializing contract...');
    
    // Only try to initialize if the user has a wallet connected
    if (window.ethereum && window.ethereum.selectedAddress) {
        await initializeContract();
    }
    
    // Set up event listener for account changes
    if (window.ethereum) {
        window.ethereum.on('accountsChanged', async () => {
            if (window.ethereum.selectedAddress) {
                await initializeContract();
            } else {
                cryptoFundContract = null;
            }
        });
        
        // Set up network change listener
        window.ethereum.on('chainChanged', async () => {
            if (window.ethereum.selectedAddress) {
                await initializeContract();
            }
        });
    }
});