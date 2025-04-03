/**
 * Main application script
 * Handles UI interaction and event listeners
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOM References
    const walletDropdownToggle = document.getElementById('walletDropdownToggle');
    const walletDropdownMenu = document.getElementById('walletDropdownMenu');
    
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
