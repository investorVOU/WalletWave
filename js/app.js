/**
 * Main application script
 * Handles UI interaction and event listeners
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Animation for campaign cards
    const campaignCards = document.querySelectorAll('.campaign-card');
    if (campaignCards.length > 0) {
        campaignCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('animate__animated', 'animate__pulse');
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('animate__animated', 'animate__pulse');
            });
        });
    }
    
    // Animate elements on scroll
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                const animationClass = element.dataset.animation || 'animate__fadeIn';
                element.classList.add('animate__animated', animationClass);
                element.classList.remove('animate-on-scroll');
            }
        });
    };
    
    // Add scroll event listener for animations
    window.addEventListener('scroll', animateOnScroll);
    // Run once on page load
    setTimeout(animateOnScroll, 500);
    
    // Campaign staking and contribution functionality
    initializeStaking();
    
    // Contribution success notification
    if (window.location.hash === '#contribution-success') {
        showToast('Contribution successful! Thank you for supporting this project.', 'success');
        // Remove the hash from the URL
        history.replaceState(null, null, ' ');
    }
});

/**
 * Initialize staking functionality
 */
function initializeStaking() {
    // Check if we're on the staking page
    const stakeButton = document.getElementById('stakeButton');
    const fundButton = document.getElementById('fundButton');
    
    if (stakeButton) {
        stakeButton.addEventListener('click', handleStaking);
    }
    
    if (fundButton) {
        fundButton.addEventListener('click', handleFunding);
    }
}

/**
 * Handle staking functionality
 */
async function handleStaking() {
    const transactionModal = document.getElementById('transactionModal');
    const txProcessing = document.getElementById('txProcessing');
    const txSuccess = document.getElementById('txSuccess');
    const txError = document.getElementById('txError');
    const txErrorMessage = document.getElementById('txErrorMessage');
    
    if (!transactionModal || !txProcessing || !txSuccess || !txError) {
        return;
    }
    
    // Show transaction modal in processing state
    transactionModal.classList.remove('hidden');
    txProcessing.classList.remove('hidden');
    txSuccess.classList.add('hidden');
    txError.classList.add('hidden');
    
    try {
        // Check if wallet is connected
        if (!selectedAccount) {
            throw new Error('Wallet not connected');
        }
        
        // Get form data
        const campaignId = document.getElementById('campaignId').value;
        const amount = parseFloat(document.getElementById('stakeAmount').value);
        const chainId = parseInt(document.getElementById('chainId').value);
        const tokenSymbol = document.getElementById('tokenSymbol').value;
        
        if (!campaignId || isNaN(amount) || amount <= 0) {
            throw new Error('Please enter a valid amount');
        }
        
        // Check if on correct network
        if (currentChainId !== chainId) {
            // Show network switch modal
            transactionModal.classList.add('hidden');
            document.getElementById('networkSwitchModal').classList.remove('hidden');
            document.getElementById('requiredNetwork').textContent = 
                NETWORKS[chainId] ? NETWORKS[chainId].name : `Chain ID ${chainId}`;
            return;
        }
        
        // This would be the actual transaction code in a real implementation
        // For demo purposes, we'll simulate a transaction
        console.log('Simulating blockchain transaction for staking...');
        
        // Generate a fake transaction hash
        const transactionHash = '0x' + Array(64).fill(0).map(() => Math.floor(Math.random() * 16).toString(16)).join('');
        
        // Wait for "blockchain confirmation"
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Save contribution to database
        const response = await fetch('/api/contribute.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                campaign_id: campaignId,
                amount: amount,
                wallet_address: selectedAccount,
                transaction_hash: transactionHash,
                staking: true
            }),
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to record stake');
        }
        
        // Update UI with transaction info
        document.getElementById('txHash').textContent = transactionHash.substring(0, 10) + '...' + transactionHash.substring(transactionHash.length - 8);
        const blockExplorerUrl = NETWORKS[chainId] ? NETWORKS[chainId].blockExplorerUrl : 'https://etherscan.io';
        document.getElementById('viewTxLink').href = `${blockExplorerUrl}/tx/${transactionHash}`;
        
        // Show success state
        txProcessing.classList.add('hidden');
        txSuccess.classList.remove('hidden');
        
    } catch (error) {
        console.error('Staking error:', error);
        
        // Show error state
        txProcessing.classList.add('hidden');
        txError.classList.remove('hidden');
        txErrorMessage.textContent = error.message || 'Transaction failed';
    }
}

/**
 * Handle direct funding (no staking)
 */
async function handleFunding() {
    const transactionModal = document.getElementById('transactionModal');
    const txProcessing = document.getElementById('txProcessing');
    const txSuccess = document.getElementById('txSuccess');
    const txError = document.getElementById('txError');
    const txErrorMessage = document.getElementById('txErrorMessage');
    
    if (!transactionModal || !txProcessing || !txSuccess || !txError) {
        return;
    }
    
    // Show transaction modal in processing state
    transactionModal.classList.remove('hidden');
    txProcessing.classList.remove('hidden');
    txSuccess.classList.add('hidden');
    txError.classList.add('hidden');
    
    try {
        // Check if wallet is connected
        if (!selectedAccount) {
            throw new Error('Wallet not connected');
        }
        
        // Get form data
        const campaignId = document.getElementById('campaignId').value;
        const amount = parseFloat(document.getElementById('fundAmount').value);
        const chainId = parseInt(document.getElementById('chainId').value);
        const tokenSymbol = document.getElementById('tokenSymbol').value;
        
        if (!campaignId || isNaN(amount) || amount <= 0) {
            throw new Error('Please enter a valid amount');
        }
        
        // Check if on correct network
        if (currentChainId !== chainId) {
            // Show network switch modal
            transactionModal.classList.add('hidden');
            document.getElementById('networkSwitchModal').classList.remove('hidden');
            document.getElementById('requiredNetwork').textContent = 
                NETWORKS[chainId] ? NETWORKS[chainId].name : `Chain ID ${chainId}`;
            return;
        }
        
        // This would be the actual transaction code in a real implementation
        // For demo purposes, we'll simulate a transaction
        console.log('Simulating blockchain transaction for funding...');
        
        // Generate a fake transaction hash
        const transactionHash = '0x' + Array(64).fill(0).map(() => Math.floor(Math.random() * 16).toString(16)).join('');
        
        // Wait for "blockchain confirmation"
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Save contribution to database
        const response = await fetch('/api/contribute.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                campaign_id: campaignId,
                amount: amount,
                wallet_address: selectedAccount,
                transaction_hash: transactionHash,
                staking: false
            }),
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to record contribution');
        }
        
        // Update UI with transaction info
        document.getElementById('txHash').textContent = transactionHash.substring(0, 10) + '...' + transactionHash.substring(transactionHash.length - 8);
        const blockExplorerUrl = NETWORKS[chainId] ? NETWORKS[chainId].blockExplorerUrl : 'https://etherscan.io';
        document.getElementById('viewTxLink').href = `${blockExplorerUrl}/tx/${transactionHash}`;
        
        // Show success state
        txProcessing.classList.add('hidden');
        txSuccess.classList.remove('hidden');
        
    } catch (error) {
        console.error('Funding error:', error);
        
        // Show error state
        txProcessing.classList.add('hidden');
        txError.classList.remove('hidden');
        txErrorMessage.textContent = error.message || 'Transaction failed';
    }
}
