// admin.js - Premium Admin Experience

// Pass PHP data to JavaScript (these will be defined in your PHP file before including this JS)
// Make sure to define these variables in your adminpage.php before including this script
/*
const monthlyLabels = <?php echo json_encode($monthly_labels); ?>;
const monthlyListings = <?php echo json_encode($monthly_listings); ?>;
const monthlyUsers = <?php echo json_encode($monthly_users); ?>;
const categoryNames = <?php echo json_encode($categories); ?>;
const categoryCounts = <?php echo json_encode($category_counts); ?>;
const userGrowthLabels = <?php echo json_encode($user_growth_labels); ?>;
const userGrowthData = <?php echo json_encode($user_growth_data); ?>;
const popularCategories = <?php echo json_encode($popular_categories); ?>;
const popularCounts = <?php echo json_encode($popular_counts); ?>;
*/

document.addEventListener('DOMContentLoaded', function() {
    // ========== SIDEBAR TOGGLE ==========
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    
    // Load saved sidebar state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar?.classList.add('collapsed');
    }
    
    // ========== DARK MODE ==========
    const darkModeBtn = document.getElementById('darkModeBtn');
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
        if (darkModeBtn) {
            darkModeBtn.innerHTML = '<i class="bx bx-sun"></i>';
        }
    }
    
    if (darkModeBtn) {
        darkModeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
            darkModeBtn.innerHTML = isDark ? '<i class="bx bx-sun"></i>' : '<i class="bx bx-moon"></i>';
            
            // Optional: Show toast notification
            showToast(`${isDark ? 'Dark' : 'Light'} mode activated`, 'info');
        });
    }
    
    // ========== TAB NAVIGATION ==========
    const navItems = document.querySelectorAll('.nav-item');
    const tabContents = {
        dashboard: document.getElementById('dashboardTab'),
        users: document.getElementById('usersTab'),
        items: document.getElementById('itemsTab'),
        reports: document.getElementById('reportsTab'),
        settings: document.getElementById('settingsTab')
    };
    
    const pageTitle = document.getElementById('pageTitle');
    const pageSubtitle = document.getElementById('pageSubtitle');
    
    const titles = {
        dashboard: { title: 'Dashboard', subtitle: 'Welcome back, ' },
        users: { title: 'User Management', subtitle: 'Manage all registered users' },
        items: { title: 'Items Management', subtitle: 'Monitor and moderate listings' },
        reports: { title: 'Analytics & Reports', subtitle: 'View platform insights' },
        settings: { title: 'System Settings', subtitle: 'Configure platform preferences' }
    };
    
    function activateTab(tabId) {
        // Hide all tabs
        Object.values(tabContents).forEach(tab => {
            if (tab) tab.classList.remove('active');
        });
        
        // Show selected tab
        if (tabContents[tabId]) {
            tabContents[tabId].classList.add('active');
        }
        
        // Update active nav item
        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-tab') === tabId) {
                item.classList.add('active');
            }
        });
        
        // Update page title with animation
        if (titles[tabId]) {
            if (pageTitle) {
                pageTitle.style.opacity = '0';
                setTimeout(() => {
                    pageTitle.textContent = titles[tabId].title;
                    pageTitle.style.opacity = '1';
                }, 150);
            }
            if (pageSubtitle) {
                pageSubtitle.textContent = titles[tabId].subtitle.includes('Welcome') 
                    ? `Welcome back, ${getUserName()} 👋` 
                    : titles[tabId].subtitle;
            }
        }
        
        // Save active tab
        localStorage.setItem('activeTab', tabId);
        
        // Refresh charts if needed
        if (tabId === 'dashboard' && typeof refreshDashboardCharts === 'function') {
            setTimeout(refreshDashboardCharts, 100);
        }
        if (tabId === 'reports' && typeof refreshReportsCharts === 'function') {
            setTimeout(refreshReportsCharts, 100);
        }
    }
    
    function getUserName() {
        const adminProfile = document.querySelector('.admin-profile h4');
        return adminProfile ? adminProfile.textContent : 'Admin';
    }
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = item.getAttribute('data-tab');
            if (tabId) activateTab(tabId);
        });
    });
    
    // Load saved tab
    const savedTab = localStorage.getItem('activeTab');
    if (savedTab && tabContents[savedTab]) {
        activateTab(savedTab);
    }
    
    // ========== ITEM SEARCH ==========
    const itemSearch = document.getElementById('itemSearch');
    const itemsTable = document.getElementById('itemsTable');
    
    if (itemSearch) {
        itemSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = itemsTable?.querySelectorAll('tbody tr');
            
            rows?.forEach(row => {
                const itemName = row.cells[1]?.textContent.toLowerCase() || '';
                const category = row.cells[2]?.textContent.toLowerCase() || '';
                const owner = row.cells[3]?.textContent.toLowerCase() || '';
                
                const matches = itemName.includes(searchTerm) || 
                               category.includes(searchTerm) || 
                               owner.includes(searchTerm);
                
                row.style.display = matches ? '' : 'none';
            });
        });
    }
    
    // ========== MODAL HANDLING ==========
    const modal = document.getElementById('userModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const modalClose = document.querySelector('.modal-close');
    const cancelBtn = document.querySelector('.cancel-btn');
    const saveUserBtn = document.getElementById('saveUserBtn');
    
    function openModal() {
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Add animation to modal content
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.animation = 'none';
                setTimeout(() => {
                    modalContent.style.animation = 'modalSlideIn 0.35s cubic-bezier(0.34, 1.2, 0.64, 1)';
                }, 10);
            }
        }
    }
    
    function closeModal() {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            // Clear form
            const inputs = modal.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type !== 'select-one') input.value = '';
                else input.selectedIndex = 0;
            });
        }
    }
    
    addUserBtn?.addEventListener('click', openModal);
    modalClose?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    
    // Close modal on outside click
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    
    saveUserBtn?.addEventListener('click', async function() {
        const name = document.getElementById('newUserName')?.value;
        const email = document.getElementById('newUserEmail')?.value;
        const password = document.getElementById('newUserPassword')?.value;
        const role = document.getElementById('newUserRole')?.value;
        
        if (!name || !email || !password) {
            showToast('Please fill in all fields', 'error');
            return;
        }
        
        // Simulate API call (replace with actual AJAX)
        showToast('User created successfully!', 'success');
        closeModal();
        
        // Reload page after 1 second
        setTimeout(() => {
            location.reload();
        }, 1000);
    });
    
    // ========== TOAST NOTIFICATION SYSTEM ==========
    function showToast(message, type = 'info') {
        // Remove existing toast
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) existingToast.remove();
        
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="bx ${type === 'success' ? 'bx-check-circle' : type === 'error' ? 'bx-x-circle' : 'bx-info-circle'}"></i>
            </div>
            <div class="toast-message">${message}</div>
            <button class="toast-close"><i class="bx bx-x"></i></button>
        `;
        
        // Add styles
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--white);
            color: var(--dark);
            padding: 14px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-lg);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            border-left: 4px solid ${type === 'success' ? '#9ec55e' : type === 'error' ? '#ff4757' : '#9ec55e'};
            font-size: 14px;
            font-weight: 500;
        `;
        
        document.body.appendChild(toast);
        
        // Add close button functionality
        toast.querySelector('.toast-close')?.addEventListener('click', () => {
            toast.remove();
        });
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    }
    
    // Add keyframe animations for toast
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--gray-600);
            padding: 0;
            margin-left: 8px;
            transition: var(--transition);
        }
        .toast-close:hover {
            color: var(--danger);
            transform: scale(1.1);
        }
    `;
    document.head.appendChild(style);
    
    // ========== STATISTICS ANIMATION ==========
    function animateNumbers() {
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(el => {
            const finalValue = parseInt(el.textContent);
            if (isNaN(finalValue)) return;
            
            let currentValue = 0;
            const duration = 1000;
            const increment = finalValue / (duration / 16);
            
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    el.textContent = finalValue;
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(currentValue);
                }
            }, 16);
        });
    }
    
    // Trigger number animation when dashboard is visible
    if (document.getElementById('dashboardTab')?.classList.contains('active')) {
        setTimeout(animateNumbers, 200);
    }
    
    // ========== RESPONSIVE SIDEBAR FOR MOBILE ==========
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.body.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    document.body.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeDistance = touchEndX - touchStartX;
        if (Math.abs(swipeDistance) < 50) return;
        
        if (swipeDistance > 0 && touchStartX < 50) {
            // Swipe right - open sidebar
            sidebar?.classList.add('mobile-show');
        } else if (swipeDistance < 0) {
            // Swipe left - close sidebar
            sidebar?.classList.remove('mobile-show');
        }
    }
    
    // Close mobile sidebar when clicking content
    const adminContent = document.querySelector('.admin-content');
    adminContent?.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar?.classList.remove('mobile-show');
        }
    });
    
    // ========== CONFIRM DELETE WITH TOAST ==========
    //const deleteBtns = document.querySelectorAll('.delete-btn');
    //deleteBtns.forEach(btn => {
    //    btn.addEventListener('click', (e) => {
    //        e.preventDefault();
    //        const confirmed = confirm('Are you sure you want to delete this item? This action cannot be undone.');
    //        if (confirmed) {
    //            showToast('Item deleted successfully', 'success');
    //           setTimeout(() => {
    //                window.location.href = btn.href;
    //           }, 500);
    //       }
    //    });
    //});
    
    // ========== SETTINGS SAVE ==========
    const saveSettingsBtn = document.querySelector('.save-settings-btn');
    saveSettingsBtn?.addEventListener('click', () => {
        showToast('Settings saved successfully!', 'success');
    });
    
    // ========== INITIALIZE CHARTS WITH REAL DATA ==========
    
    // Dashboard Activity Chart
    const activityCtx = document.getElementById('activityChart')?.getContext('2d');
    if (activityCtx && typeof monthlyLabels !== 'undefined' && monthlyLabels.length > 0) {
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [
                    {
                        label: 'Listings',
                        data: monthlyListings,
                        borderColor: '#9ec55e',
                        backgroundColor: 'rgba(158, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#9ec55e',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'New Users',
                        data: monthlyUsers,
                        borderColor: '#123426',
                        backgroundColor: 'rgba(18, 52, 38, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#123426',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });
    }

    // Category Distribution Chart (Doughnut)
    const categoryCtx = document.getElementById('categoryChart')?.getContext('2d');
    if (categoryCtx && typeof categoryNames !== 'undefined' && categoryNames.length > 0) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryNames,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: ['#9ec55e', '#123426', '#7aa33d', '#1a4a35', '#b8da8a', '#4a7c59'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // User Growth Chart (Reports Tab)
    const userGrowthCtx = document.getElementById('userGrowthChart')?.getContext('2d');
    if (userGrowthCtx && typeof userGrowthLabels !== 'undefined' && userGrowthLabels.length > 0) {
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: userGrowthLabels,
                datasets: [{
                    label: 'Total Users',
                    data: userGrowthData,
                    borderColor: '#123426',
                    backgroundColor: 'rgba(18, 52, 38, 0.05)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#123426',
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Users'
                        }
                    }
                }
            }
        });
    }

    // Popular Categories Chart (Reports Tab)
    const popularCtx = document.getElementById('popularCategoriesChart')?.getContext('2d');
    if (popularCtx && typeof popularCategories !== 'undefined' && popularCategories.length > 0) {
        new Chart(popularCtx, {
            type: 'bar',
            data: {
                labels: popularCategories,
                datasets: [{
                    label: 'Number of Items',
                    data: popularCounts,
                    backgroundColor: '#9ec55e',
                    borderRadius: 8,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Items: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        title: {
                            display: true,
                            text: 'Number of Items'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Category'
                        }
                    }
                }
            }
        });
    }

    // Optional: Refresh function for chart period dropdown
    document.getElementById('chartPeriod')?.addEventListener('change', function(e) {
        const period = e.target.value;
        if (period === 'Last Year') {
            showToast('Loading yearly data...', 'info');
            // You can implement 12-month data fetch via AJAX here
        }
    });
    
    // Keep the old init function for compatibility (but it won't override the real data)
    function refreshDashboardCharts() {
        // Refresh logic here if needed
    }
    
    function refreshReportsCharts() {
        // Reports charts logic here if needed
    }
});