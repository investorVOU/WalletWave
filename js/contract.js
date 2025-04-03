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
        
        // Get the signer (account)
        const signer = provider.getSigner();
        
        // Create the contract instance
        cryptoFundContract = new ethers.Contract(
            contractData.address,
            contractData.abi,
            signer
        );
        
        console.log('Contract initialized:', cryptoFundContract.address);
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
    }
});