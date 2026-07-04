document.addEventListener('DOMContentLoaded', function() {
    // Standard chat UI controls (Auto Scroll & Plus menu toggling)
    const canvas = document.getElementById('chatMessageCanvas');
    if (canvas) { canvas.scrollTop = canvas.scrollHeight; }

    const plusBtn = document.getElementById('chatPlusBtn');
    const actionMenu = document.getElementById('chatActionMenu');
    if (plusBtn && actionMenu) {
        plusBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            actionMenu.style.display = (actionMenu.style.display === 'block') ? 'none' : 'block';
        });
        document.addEventListener('click', function() {
            actionMenu.style.display = 'none';
            if(plusBtn) plusBtn.classList.remove('active');
        });
    }

    // Modal Visibility Controls
    const openModalBtn = document.getElementById('openOrderModalBtn');
    const orderModal = document.getElementById('orderFormModal');
    const closeModalBtn = document.getElementById('closeOrderModalBtn');

    if (openModalBtn && orderModal) {
        openModalBtn.addEventListener('click', function() { orderModal.style.display = 'flex'; });
    }
    if (closeModalBtn && orderModal) {
        closeModalBtn.addEventListener('click', function() { orderModal.style.display = 'none'; });
    }

    // Dynamic Live Typeahead Search implementation
    const locationInput = document.getElementById('proposalLocationInput');
    const suggestionsBox = document.getElementById('proposalLocationSuggestions');
    let debounceTimer;

    if (locationInput && suggestionsBox) {
        locationInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                suggestionsBox.innerHTML = '';
                return;
            }

            // Debounce API calls to prevent flooding the OpenStreetMap service
            debounceTimer = setTimeout(() => {
                fetch(`get_locations.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(locationName => {
                                const div = document.createElement('div');
                                div.className = 'location-suggestion-item';
                                div.textContent = locationName;
                                
                                // On suggestion click: apply value and close dropdown
                                div.addEventListener('click', function() {
                                    locationInput.value = locationName;
                                    suggestionsBox.innerHTML = '';
                                });
                                suggestionsBox.appendChild(div);
                            });
                        }
                    })
                    .catch(err => console.error('Error fetching locations:', err));
            }, 300);
        });

        // Close search list if user clicks outside form fields
        document.addEventListener('click', function(e) {
            if (e.target !== locationInput) {
                suggestionsBox.innerHTML = '';
            }
        });
    }
});

// Gateway handler functions for buyer checkout popups
function openPaymentModal(orderId, price) {
    const payModal = document.getElementById('paymentModal');
    if (payModal) {
        document.getElementById('payFormOrderId').value = orderId;
        document.getElementById('payAmountLabel').innerText = 'RM ' + parseFloat(price).toFixed(2);
        payModal.style.display = 'flex';
    }
}

function closePaymentModal() {
    const payModal = document.getElementById('paymentModal');
    if (payModal) payModal.style.display = 'none';
}

function toggleBankSelect() {
    const method = document.getElementById('paymentMethodSelect').value;
    const bankGroup = document.getElementById('bankDropdownGroup');
    const bankSelect = document.getElementById('paymentBankSelect');
    
    if (method === 'Online Banking') {
        bankGroup.style.display = 'block';
        bankSelect.required = true;
    } else {
        bankGroup.style.display = 'none';
        bankSelect.required = false;
    }
}

function triggerPreFilledProposal(itemId) {
    const itemDropdown = document.getElementById('modalItemSelectDropdown');
    const orderModal = document.getElementById('orderFormModal');
    const locationInput = document.getElementById('proposalLocationInput');
    
    if(itemDropdown && orderModal) {
        // Set dropdown directly to requested item value
        itemDropdown.value = itemId;
        
        // Show your existing modal 
        orderModal.style.display = "block";
        
        // Focus user instantly on the missing piece: Meetup Location!
        if(locationInput) {
            locationInput.focus();
        }
    }
}