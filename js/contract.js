/**
 * Contract interaction functionality
 * Handles interactions with the CryptoFund smart contract
 */

// Global contract instance
let cryptoFundContract = null;

// Load contract data
async function loadContractData() {
    try {
        const response = await fetch('/contract-data.json');
        if (!response.ok) {
            throw new Error('Failed to load contract data');
        }
        return await response.json();
    } catch (error) {
        console.error('Error loading contract data:', error);
        return null;
    }
}

// Initialize contract
async function initializeContract() {
    // Check if Web3 is available
    if (!window.ethereum && !window.web3) {
        console.error('Web3 not available');
        return false;
    }
    
    try {
        // Get the contract data
        const contractData = await loadContractData();
        if (!contractData) {
            return false;
        }
        
        // Get the provider
        const provider = new ethers.providers.Web3Provider(window.ethereum);
        
        // Get the current network
        const network = await provider.getNetwork();
        console.log('Current network:', network);
        
        // Check if we're on the correct network
        if (contractData.networkId && network.chainId !== contractData.networkId) {
            console.warn(`Contract is deployed on ${contractData.networkName} (chainId: ${contractData.networkId}), but you're connected to chainId: ${network.chainId}`);
            
            // Prompt user to switch networks
            try {
                await window.ethereum.request({
                    method: 'wallet_switchEthereumChain',
                    params: [{ chainId: '0x' + contractData.networkId.toString(16) }], // Convert to hex
                });
                
                // Wait for the network to switch and reload the page
                return false;
            } catch (switchError) {
                // This error code indicates that the chain has not been added to MetaMask
                if (switchError.code === 4902) {
                    try {
                        // If chain is not available, add it to the wallet
                        let networkParams = {};
                        
                        // Dynamically set network parameters based on chain ID
                        switch (contractData.networkId) {
                            case 11155111: // Sepolia
                                networkParams = {
                                    chainId: '0x' + contractData.networkId.toString(16),
                                    chainName: 'Sepolia Testnet',
                                    nativeCurrency: {
                                        name: 'Sepolia ETH',
                                        symbol: 'SepoliaETH',
                                        decimals: 18
                                    },
                                    rpcUrls: ['https://sepolia.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1'],
                                    blockExplorerUrls: ['https://sepolia.etherscan.io/']
                                };
                                break;
                            case 5: // Goerli
                                networkParams = {
                                    chainId: '0x' + contractData.networkId.toString(16),
                                    chainName: 'Goerli Testnet',
                                    nativeCurrency: {
                                        name: 'Goerli ETH',
                                        symbol: 'GoerliETH',
                                        decimals: 18
                                    },
                                    rpcUrls: ['https://goerli.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1'],
                                    blockExplorerUrls: ['https://goerli.etherscan.io/']
                                };
                                break;
                            case 80001: // Mumbai
                                networkParams = {
                                    chainId: '0x' + contractData.networkId.toString(16),
                                    chainName: 'Mumbai Testnet',
                                    nativeCurrency: {
                                        name: 'Testnet MATIC',
                                        symbol: 'tMATIC',
                                        decimals: 18
                                    },
                                    rpcUrls: ['https://rpc-mumbai.maticvigil.com'],
                                    blockExplorerUrls: ['https://mumbai.polygonscan.com/']
                                };
                                break;
                            default:
                                // Default parameters (using whatever data we have)
                                networkParams = {
                                    chainId: '0x' + contractData.networkId.toString(16),
                                    chainName: contractData.networkName,
                                    nativeCurrency: {
                                        name: 'ETH',
                                        symbol: 'ETH',
                                        decimals: 18
                                    },
                                    rpcUrls: ['https://sepolia.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1'], // Default to Sepolia
                                    blockExplorerUrls: ['https://sepolia.etherscan.io/']
                                };
                        }
                        
                        await window.ethereum.request({
                            method: 'wallet_addEthereumChain',
                            params: [networkParams],
                        });
                        
                        return false;
                    } catch (addError) {
                        console.error('Error adding Ethereum chain:', addError);
                        showToast('Please manually switch to ' + contractData.networkName + ' to interact with the contract', 'error');
                        return false;
                    }
                } else {
                    console.error('Error switching network:', switchError);
                    showToast('Please manually switch to ' + contractData.networkName + ' to interact with the contract', 'error');
                    return false;
                }
            }
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
        
        return true;
    } catch (error) {
        console.error('Error initializing contract:', error);
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
    if (!cryptoFundContract) {
        if (!(await initializeContract())) {
            throw new Error('Contract not initialized');
        }
    }
    
    try {
        // Convert amount to wei
        const amountInWei = ethers.utils.parseEther(amount.toString());
        
        // Send the transaction
        const tx = await cryptoFundContract.contribute(campaignId, {
            value: amountInWei
        });
        
        // Wait for transaction to be mined
        const receipt = await tx.wait();
        
        return {
            success: true,
            transactionHash: receipt.transactionHash
        };
    } catch (error) {
        console.error('Error contributing to campaign:', error);
        throw error;
    }
}

// Contribute with staking
async function contributeWithStaking(campaignId, amount, stakingPeriodInDays) {
    if (!cryptoFundContract) {
        if (!(await initializeContract())) {
            throw new Error('Contract not initialized');
        }
    }
    
    try {
        // Convert amount to wei
        const amountInWei = ethers.utils.parseEther(amount.toString());
        
        // Send the transaction
        const tx = await cryptoFundContract.contributeWithStaking(
            campaignId,
            stakingPeriodInDays,
            {
                value: amountInWei
            }
        );
        
        // Wait for transaction to be mined
        const receipt = await tx.wait();
        
        return {
            success: true,
            transactionHash: receipt.transactionHash
        };
    } catch (error) {
        console.error('Error staking in campaign:', error);
        throw error;
    }
}

// Get campaign details
async function getCampaign(campaignId) {
    if (!cryptoFundContract) {
        if (!(await initializeContract())) {
            throw new Error('Contract not initialized');
        }
    }
    
    try {
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
        throw error;
    }
}

// Get staking details
async function getStakingContribution(campaignId, address) {
    if (!cryptoFundContract) {
        if (!(await initializeContract())) {
            throw new Error('Contract not initialized');
        }
    }
    
    try {
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