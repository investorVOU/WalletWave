/**
 * Main application script
 * Handles UI interaction and event listeners
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOM References
    const connectWalletBtns = document.querySelectorAll('.connect-wallet-btn');
    const walletDropdownToggle = document.getElementById('walletDropdownToggle');
    const walletDropdownMenu = document.getElementById('walletDropdownMenu');
    const disconnectWalletBtn = document.getElementById('disconnectWalletBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const continueBtn = document.getElementById('continueBtn');
    const retryBtn = document.getElementById('retryBtn');
    const closeErrorBtn = document.getElementById('closeErrorBtn');
    const copyAddressBtn = document.getElementById('copyAddressBtn');
    const walletModalOverlay = document.getElementById('walletModalOverlay');
    
    // Connect wallet buttons
    connectWalletBtns.forEach(btn => {
        btn.addEventListener('click', async function() {
            // Only connect if not already connected
            if (!btn.classList.contains('connected')) {
                await connectWallet();
            } else {
                // If already connected, show the dropdown
                if (walletDropdownToggle) {
                    walletDropdownToggle.click();
                }
            }
        });
    });
    
    // Wallet dropdown toggle (if exists)
    if (walletDropdownToggle) {
        walletDropdownToggle.addEventListener('click', function() {
            walletDropdownMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!walletDropdownToggle.contains(event.target) && !walletDropdownMenu.contains(event.target)) {
                walletDropdownMenu.classList.add('hidden');
            }
        });
    }
    
    // Disconnect wallet button
    if (disconnectWalletBtn) {
        disconnectWalletBtn.addEventListener('click', async function() {
            await disconnectWallet();
        });
    }
    
    // Modal close button
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            hideConnectionModal();
        });
    }
    
    // Modal overlay click
    if (walletModalOverlay) {
        walletModalOverlay.addEventListener('click', function() {
            hideConnectionModal();
        });
    }
    
    // Continue button in success state
    if (continueBtn) {
        continueBtn.addEventListener('click', function() {
            hideConnectionModal();
        });
    }
    
    // Retry button in error state
    if (retryBtn) {
        retryBtn.addEventListener('click', async function() {
            hideConnectionModal();
            setTimeout(async () => {
                await connectWallet();
            }, 500);
        });
    }
    
    // Close error button
    if (closeErrorBtn) {
        closeErrorBtn.addEventListener('click', function() {
            hideConnectionModal();
        });
    }
    
    // Copy address button
    if (copyAddressBtn) {
        copyAddressBtn.addEventListener('click', function() {
            copyAddressToClipboard();
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
});
