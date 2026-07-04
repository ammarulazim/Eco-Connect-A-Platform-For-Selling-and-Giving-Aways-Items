// auth trasition
const cointaner = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');

registerBtn.addEventListener('click', () => {
    cointaner.classList.add('active');
});

loginBtn.addEventListener('click', () => {
    cointaner.classList.remove('active');
});

document.addEventListener('DOMContentLoaded', function() {
    const locInput = document.getElementById('regLocation');
    const suggestionsBox = document.getElementById('locationSuggestions');

    if (locInput && suggestionsBox) {
        let timeout = null;

        locInput.addEventListener('input', function() {
            const val = this.value.trim();

            // Clear previous debounced timeouts to prevent redundant hitting
            clearTimeout(timeout);

            if (val.length < 2) {
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';
                return;
            }

            // Debounce for 250ms while the user is typing rapidly
            timeout = setTimeout(() => {
                fetch(`get_locations.php?q=${encodeURIComponent(val)}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.style.padding = '12px 16px';
                                div.style.cursor = 'pointer';
                                div.style.color = '#1e293b';
                                div.textContent = item;
                                
                                // On click, apply value and hide options box
                                div.addEventListener('click', function() {
                                    locInput.value = this.textContent;
                                    suggestionsBox.innerHTML = '';
                                    suggestionsBox.style.display = 'none';
                                });
                                
                                suggestionsBox.appendChild(div);
                            });
                            suggestionsBox.style.display = 'block';
                        } else {
                            // Optional: Show fallback item if nothing hits database records
                            suggestionsBox.innerHTML = '<div style="padding:12px 16px; color:#64748b; font-style:italic;">No region found</div>';
                            suggestionsBox.style.display = 'block';
                        }
                    })
                    .catch(err => console.error('Error fetching dynamic location indexes:', err));
            }, 250);
        });

        // Close menu dropdown if user clicks away outside input
        document.addEventListener('click', function(e) {
            if (e.target !== locInput && e.target !== suggestionsBox) {
                suggestionsBox.style.display = 'none';
            }
        });
    }
});