// --- CAROUSEL SLIDER ENGINE ---
let slideIndex = 0;
let slideInterval;

function initCarousel() {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    
    // Check if carousel exists on the current page context
    if (slides.length === 0) return; 

    // Reset loop constraints
    if (slideIndex >= slides.length) { slideIndex = 0; }
    if (slideIndex < 0) { slideIndex = slides.length - 1; }

    // Strip active tracking classes from assets
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    // Apply visibility to current index target frame
    slides[slideIndex].classList.add('active');
    if (dots[slideIndex]) dots[slideIndex].classList.add('active');
}

// Controls manual skipping via arrows
function moveSlide(n) {
    clearInterval(slideInterval); // Reset timer on manual interaction
    slideIndex += n;
    initCarousel();
    startAutoSlide(); // Restart automatic loop sequence
}

// Controls dot jumping
function currentSlide(n) {
    clearInterval(slideInterval);
    slideIndex = n;
    initCarousel();
    startAutoSlide();
}

// Background auto-rotation interval rule loop (5000ms = 5 seconds)
function startAutoSlide() {
    slideInterval = setInterval(() => {
        slideIndex++;
        initCarousel();
    }, 5000);
}

// Initialize components safely once DOM finishes construction
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll('.carousel-slide');
    if (slides.length > 0) {
        initCarousel();
        startAutoSlide();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('itemSearch');
    const itemCards = document.querySelectorAll('.item-card');
    const noResultsMessage = document.getElementById('noResultsMessage'); // Target the fallback box

    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase().trim();
            let visibleCount = 0; // Tracking counter

            itemCards.forEach(card => {
                const itemName = card.querySelector('h3').textContent.toLowerCase();
                const itemDesc = card.querySelector('p').textContent.toLowerCase();

                if (itemName.includes(query) || itemDesc.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++; // Found a match!
                } else {
                    card.style.display = 'none';
                }
            });

            // Toggle the visibility of the "No items found" message container
            if (visibleCount === 0) {
                if (noResultsMessage) noResultsMessage.style.display = 'block';
            } else {
                if (noResultsMessage) noResultsMessage.style.display = 'none';
            }
        });

        // Optional focus border styling tracks
        searchInput.addEventListener('focus', function() {
            this.style.borderColor = '#9ec55e';
        });
        searchInput.addEventListener('blur', function() {
            this.style.borderColor = 'rgba(255,255,255,0.1)';
        });
    }
});