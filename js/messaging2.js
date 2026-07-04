document.addEventListener('DOMContentLoaded', function() {
    // 1. Auto Scroll Layout
    const canvas = document.getElementById('chatMessageCanvas');
    if (canvas) { canvas.scrollTop = canvas.scrollHeight; }

    // 2. Action Menu Subpopover Handlers
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

    // 3. Trade Order Form Setup Controls
    const openModalBtn = document.getElementById('openOrderModalBtn');
    const orderModal = document.getElementById('orderFormModal');
    const closeModalBtn = document.getElementById('closeOrderModalBtn');
    const locationDropdown = document.getElementById('proposalLocationDropdown');

    if (openModalBtn && orderModal) {
        openModalBtn.addEventListener('click', function() {
            orderModal.style.display = 'flex';
            
            const datalist = document.getElementById('locationSuggestions');
            if (datalist) {
                fetch('api/get_locations.php') // Enforce your sign-up API target path location
                    .then(res => res.json())
                    .then(data => {
                        datalist.innerHTML = ''; // Clear prior suggestions
                        data.forEach(loc => {
                            // Create interactive suggestion layout row node strings
                            datalist.innerHTML += `<option value="${loc.location_name}">`;
                        });
                    })
                    .catch(() => {
                        // Quick regional fallbacks if API stream is unreachable
                        datalist.innerHTML = `
                            <option value="MMU Cyberjaya Campus">
                            <option value="MMU Melaka Campus">
                            <option value="Puchong Community Center">
                        `;
                    });
            }
        });
    }
    if (closeModalBtn && orderModal) {
        closeModalBtn.addEventListener('click', function() {
            orderModal.style.display = 'none';
        });
    }
});

// 4. BUYER ACTION PAYMENT POPUP TRIGGERS
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