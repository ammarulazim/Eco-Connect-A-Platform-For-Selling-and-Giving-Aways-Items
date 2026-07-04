// about us trasition
document.addEventListener("DOMContentLoaded", function () {
    // 1. Target all structural containers that reveal on scroll
    const revealItems = document.querySelectorAll('.reveal-item');

    const observerOptions = {
        root: null,          
        rootMargin: "0px",   
        threshold: 0.25      // Stays at 25% visibility for a deliberate, non-rushed feel
    };

    const scrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;

                // 2. STAGGER EFFECT: If the section contains grid boxes, delay them sequentially
                const childCards = target.querySelectorAll('.sdg-card, .feature-box');
                
                if (childCards.length > 0) {
                    // Loop through each sub-card inside the section grid
                    childCards.forEach((card, index) => {
                        // Apply an incremental delay (0.15s, 0.30s, 0.45s, etc.)
                        card.style.animationDelay = `${index * 0.15}s`;
                        card.classList.add('pop-active');
                    });
                    
                    // Also make sure the main container layout wrapper becomes active
                    target.classList.add('active');
                } else {
                    // Normal behavior for non-grid text wrappers (like your intro-wrapper)
                    target.classList.add('active');
                }

                // Stop tracking this specific element once it has naturally activated
                observer.unobserve(target);
            }
        });
    }, observerOptions);

    revealItems.forEach(item => {
        scrollObserver.observe(item);
    });
});