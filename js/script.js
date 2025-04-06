/**
 * Main script for DoodleSense AI landing page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize any interactive elements on the home page
    initializeFeaturesAnimation();
    initializeScrollBehavior();
});

/**
 * Add animation effects to features section
 */
function initializeFeaturesAnimation() {
    const featureCards = document.querySelectorAll('.feature-card');
    
    if (featureCards.length === 0) return;
    
    // Add animation when scrolling to features section
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const featureObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = 1;
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    featureCards.forEach((card, index) => {
        card.style.opacity = 0;
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.4s ease-out ${index * 0.1}s, transform 0.4s ease-out ${index * 0.1}s`;
        featureObserver.observe(card);
    });
}

/**
 * Initialize smooth scroll behavior for anchor links
 */
function initializeScrollBehavior() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                window.scrollTo({
                    top: targetElement.offsetTop - 70, // Offset for fixed header
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Function to handle mobile navigation menu
 */
function toggleMobileMenu() {
    const navElement = document.querySelector('nav ul');
    navElement.classList.toggle('show-mobile');
}

/**
 * Initialize any modals on the page
 */
function initializeModals() {
    const modals = document.querySelectorAll('.modal');
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalCloseButtons = document.querySelectorAll('.close-modal');
    
    if (modals.length === 0) return;
    
    // Open modal
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Close modal with X button
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close modal when clicking outside content
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close modal with ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
}

// Call modal initialization if needed
initializeModals();
