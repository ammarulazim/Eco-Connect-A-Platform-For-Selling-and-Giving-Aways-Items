// admin.js

const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

// Sidebar Toggle
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('mobile-show');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    });
}

// Tab Switching
const menuItems = document.querySelectorAll('.nav-item');
const tabs = ['dashboard', 'users', 'items', 'reports', 'settings'];

menuItems.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        const tab = item.getAttribute('data-tab');
        
        menuItems.forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        
        tabs.forEach(t => {
            const tabElement = document.getElementById(`${t}Tab`);
            if (tabElement) tabElement.classList.remove('active');
        });
        
        const activeTab = document.getElementById(`${tab}Tab`);
        if (activeTab) activeTab.classList.add('active');
        
        // Update page title
        const pageTitles = {
            dashboard: 'Dashboard',
            users: 'User Management',
            items: 'Item Inventory',
            reports: 'Analytics Reports',
            settings: 'System Settings'
        };
        
        const titleElement = document.getElementById('pageTitle');
        if (titleElement) titleElement.textContent = pageTitles[tab] || 'Dashboard';
    });
});

// Dark Mode
const darkBtn = document.getElementById('darkModeBtn');

if (darkBtn) {
    darkBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const icon = darkBtn.querySelector('i');
        if (document.body.classList.contains('dark-mode')) {
            icon.className = 'bx bx-sun';
        } else {
            icon.className = 'bx bx-moon';
        }
        // Save preference
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    });
    
    // Load saved preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        darkBtn.querySelector('i').className = 'bx bx-sun';
    }
}

// Item Search
const itemSearch = document.getElementById('itemSearch');
if (itemSearch) {
    itemSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const table = document.getElementById('itemsTable');
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const itemName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                row.style.display = itemName.includes(searchTerm) ? '' : 'none';
            });
        }
    });
}

// Modal Functionality
const modal = document.getElementById('userModal');
const addUserBtn = document.getElementById('addUserBtn');
const modalClose = document.querySelector('.modal-close');
const cancelBtn = document.querySelector('.cancel-btn');
const saveUserBtn = document.getElementById('saveUserBtn');

function openModal() {
    if (modal) modal.classList.add('active');
}

function closeModal() {
    if (modal) modal.classList.remove('active');
}

if (addUserBtn) addUserBtn.addEventListener('click', openModal);
if (modalClose) modalClose.addEventListener('click', closeModal);
if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

if (saveUserBtn) {
    saveUserBtn.addEventListener('click', function() {
        const name = document.getElementById('newUserName')?.value;
        const email = document.getElementById('newUserEmail')?.value;
        const password = document.getElementById('newUserPassword')?.value;
        const role = document.getElementById('newUserRole')?.value;
        
        if (name && email && password) {
            // Submit form via AJAX or redirect
            window.location.href = `add_user.php?name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&role=${role}`;
        } else {
            alert('Please fill in all fields');
        }
    });
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (modal && e.target === modal) closeModal();
});

// Initialize Charts
let activityChart, categoryChart, userGrowthChart, popularCategoriesChart;

function initCharts() {
    const ctx1 = document.getElementById('activityChart')?.getContext('2d');
    const ctx2 = document.getElementById('categoryChart')?.getContext('2d');
    const ctx3 = document.getElementById('userGrowthChart')?.getContext('2d');
    const ctx4 = document.getElementById('popularCategoriesChart')?.getContext('2d');
    
    // Monthly Activity Chart
    if (ctx1) {
        const monthlyLabels = [];
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            monthlyLabels.push(date.toLocaleString('default', { month: 'short' }));
        }
        
        activityChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Transactions',
                    data: typeof monthlyData !== 'undefined' ? monthlyData : [125, 150, 180, 220, 280, 340],
                    borderColor: '#008b8b',
                    backgroundColor: 'rgba(0, 139, 139, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#008b8b',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    
    // Category Distribution Chart
    if (ctx2) {
        categoryChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Furniture', 'Electronics', 'Books', 'Clothing', 'Other'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: ['#008b8b', '#20b2aa', '#ffa500', '#ff6b6b', '#bdc3c7'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    
    // User Growth Chart
    if (ctx3) {
        userGrowthChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Users',
                    data: [45, 52, 68, 74, 89, 102],
                    backgroundColor: '#008b8b',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    
    // Popular Categories Chart
    if (ctx4) {
        popularCategoriesChart = new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: ['Furniture', 'Electronics', 'Books', 'Clothing', 'Toys'],
                datasets: [{
                    label: 'Items Count',
                    data: [142, 98, 76, 54, 32],
                    backgroundColor: '#20b2aa',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
}

// Call chart initialization
document.addEventListener('DOMContentLoaded', initCharts);

// Switch tab function for "View All" button
function switchTab(tabName) {
    const targetItem = document.querySelector(`.nav-item[data-tab="${tabName}"]`);
    if (targetItem) targetItem.click();
}

// Handle window resize for sidebar
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-show');
    }
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('mobile-show')) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('mobile-show');
        }
    }
});