<!-- admin.php -->

<?php
include 'database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

/* DASHBOARD STATS */
$user_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users")
)['total'];

$item_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM items")
)['total'];

// Get active listings count
$active_listings = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE status = 'available'")
)['total'] ?? 0;

// MONTHLY ACTIVITY DATA (Last 6 months)
$monthly_labels = [];
$monthly_listings = [];
$monthly_users = [];

// Get last 6 months
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $monthly_labels[] = $month_name;
    
    // Count users registered in this month
    $user_query = mysqli_query($conn, "SELECT COUNT(*) AS count FROM users 
        WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'");
    $user_count_month = mysqli_fetch_assoc($user_query)['count'];
    $monthly_users[] = $user_count_month;
    
    // Count items listed in this month
    $item_query = mysqli_query($conn, "SELECT COUNT(*) AS count FROM items 
        WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'");
    $item_count_month = mysqli_fetch_assoc($item_query)['count'];
    $monthly_listings[] = $item_count_month;
}

// CATEGORY DISTRIBUTION (for donut chart)
$category_query = mysqli_query($conn, "SELECT category, COUNT(*) AS count 
    FROM items 
    GROUP BY category 
    ORDER BY count DESC");
$categories = [];
$category_counts = [];
while ($cat = mysqli_fetch_assoc($category_query)) {
    $categories[] = $cat['category'];
    $category_counts[] = $cat['count'];
}

// USER GROWTH DATA (for reports tab)
$user_growth_labels = [];
$user_growth_data = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $user_growth_labels[] = $month_name;
    
    $growth_query = mysqli_query($conn, "SELECT COUNT(*) AS count FROM users 
        WHERE DATE_FORMAT(created_at, '%Y-%m') <= '$month'");
    $cumulative_count = mysqli_fetch_assoc($growth_query)['count'];
    $user_growth_data[] = $cumulative_count;
}

// POPULAR CATEGORIES (for reports tab)
$popular_cats_query = mysqli_query($conn, "SELECT category, COUNT(*) AS count 
    FROM items 
    GROUP BY category 
    ORDER BY count DESC 
    LIMIT 5");
$popular_categories = [];
$popular_counts = [];
while ($pop = mysqli_fetch_assoc($popular_cats_query)) {
    $popular_categories[] = $pop['category'];
    $popular_counts[] = $pop['count'];
}

?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/admin1.css">
<link rel="shortcut icon" type="image/png" href="images/logo/logo.png" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<title>Admin | Eco-Connect</title>

<main class="admin-page">
    <div class="admin-container">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo"> 
                    <span>
                        <img class="logo1" src="images/logo/logo1.png" alt="Logo">
                    </span>
                    <span>Eco-Connect</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class='bx bx-menu-alt-left'></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active" data-tab="dashboard">
                        <a href="#">
                            <i class='bx bx-grid-alt'></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item" data-tab="users">
                        <a href="#">
                            <i class='bx bx-group'></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item" data-tab="items">
                        <a href="#">
                            <i class='bx bx-package'></i>
                            <span>Items</span>
                        </a>
                    </li>
                    <!--
                    <li class="nav-item" data-tab="transactions">
                        <a href="#">
                            <i class='bx bx-transfer-alt'></i>
                            <span>Transactions</span>
                        </a>
                    </li>
                    -->
                    <li class="nav-item" data-tab="reports">
                        <a href="#">
                            <i class='bx bx-bar-chart-alt-2'></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li class="nav-item" data-tab="settings">
                        <a href="#">
                            <i class='bx bx-cog'></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-profile">
                    <img src="https://ui-avatars.com/api/?background=9ec55e&color=fff&name=<?php echo urlencode($_SESSION['username']); ?>" alt="Admin">
                    <div>
                        <h4><?php echo ucfirst($_SESSION['username']); ?></h4>
                        <p>Administrator</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class='bx bx-log-out'></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <section class="admin-content">
            <div class="topbar">
                <div>
                    <h1 id="pageTitle">Dashboard</h1>
                    <p id="pageSubtitle">Welcome back, <?php echo ucfirst($_SESSION['username']); ?> 👋</p>
                </div>
                <div class="topbar-actions">
                    <button class="theme-toggle" id="darkModeBtn">
                        <i class='bx bx-moon'></i>
                    </button>
                    <!--
                    <button class="notifications-btn">
                        <i class='bx bx-bell'></i>
                        <span class="badge">3</span>
                    </button>
                    -->
                </div>
            </div>

            <!-- DASHBOARD TAB -->
            <section class="tab-content active" id="dashboardTab">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class='bx bx-user'></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Users</h3>
                            <p class="stat-number"><?php echo $user_count; ?></p>
                            <span class="stat-trend positive">+12% <i class='bx bx-up-arrow-alt'></i></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class='bx bx-package'></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Listings</h3>
                            <p class="stat-number"><?php echo $item_count; ?></p>
                            <span class="stat-trend positive">+8% <i class='bx bx-up-arrow-alt'></i></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class='bx bx-leaf'></i>
                        </div>
                        <div class="stat-info">
                            <h3>Active Listings</h3>
                            <p class="stat-number"><?php echo $active_listings; ?></p>
                            <span class="stat-trend positive">+5% <i class='bx bx-up-arrow-alt'></i></span>
                        </div>
                    </div>
                </div>

                <!-- CHARTS SECTION -->
                <div class="charts-row">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Monthly Activity</h3>
                            <select id="chartPeriod">
                                <option>Last 6 Months</option>
                                <option>Last Year</option>
                            </select>
                        </div>
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Category Distribution</h3>
                            <i class='bx bx-dots-horizontal-rounded'></i>
                        </div>
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- USERS TAB -->
            <section class="tab-content" id="usersTab">
                <div class="table-card">
                    <div class="table-header">
                        <h3>User Management</h3>
                        <button class="add-btn" id="addUserBtn">+ Add New User</button>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Listings</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
                                while($user = mysqli_fetch_assoc($users)) {
                                    $listing_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) AS total FROM items WHERE user_id = " . $user['user_id']
                                    ))['total'];
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo $listing_count; ?></td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td><span class="status-badge status-active">Active</span></td>
                                    <td>
                                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="action-btn edit-btn"><i class='bx bx-edit-alt'></i></a>
                                        <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this user?')"><i class='bx bx-trash'></i></a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ITEMS TAB -->
            <section class="tab-content" id="itemsTab">
                <div class="table-card">
                    <div class="table-header">
                        <h3>Listing Management</h3>
                        <div class="search-box">
                            <i class='bx bx-search'></i>
                            <input type="text" id="itemSearch" placeholder="Search items...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Owner</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $items = mysqli_query($conn,
                                    "SELECT items.*, users.username 
                                    FROM items 
                                    JOIN users ON items.user_id = users.user_id 
                                    ORDER BY items.created_at DESC");

                                while($item = mysqli_fetch_assoc($items)) {
                                ?>
                                <tr>
                                    <td><img src="images/uploads/<?php echo $item['username'] . "/" . $item['item_image']; ?>" class="item-thumbnail" alt="Item"></td>
                                    <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['category'] ?? 'Others'); ?></td>
                                    <td><?php echo htmlspecialchars($item['username']); ?></td>
                                    <td><?php echo $item['item_price'] == 0 ? 'Free' : '$' . $item['item_price']; ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($item['status'] ?? 'available'); ?>"><?php echo ucfirst($item['status'] ?? 'Available'); ?></span></td>
                                    <td>
                                        <a href="edititem.php?id=<?php echo $item['item_id']; ?>" class="action-btn edit-btn"><i class='bx bx-edit-alt'></i></a>
                                        <a href="deleteitem.php?id=<?php echo $item['item_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this listing?')"><i class='bx bx-trash'></i></a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- REPORTS TAB -->
            <section class="tab-content" id="reportsTab">
                <div class="charts-row">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>User Growth</h3>
                        </div>
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Popular Categories</h3>
                        </div>
                        <canvas id="popularCategoriesChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- SETTINGS TAB -->
            <section class="tab-content" id="settingsTab">
                <div class="settings-card">
                    <h3>System Settings</h3>
                    <div class="settings-form">
                        <div class="setting-item">
                            <label>Site Name</label>
                            <input type="text" value="Eco-Connect" class="setting-input">
                        </div>
                        <div class="setting-item">
                            <label>Contact Email</label>
                            <input type="email" value="admin@ecoconnect.com" class="setting-input">
                        </div>
                        <div class="setting-item">
                            <label>Items per Page</label>
                            <select class="setting-input">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                        </div>
                        <button class="save-settings-btn">Save Changes</button>
                    </div>
                </div>
            </section>
        </section>
    </div>
</main>

<!-- Add User Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New User</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <input type="text" id="newUserName" placeholder="Full Name">
            <input type="email" id="newUserEmail" placeholder="Email">
            <input type="text" id="newUserPassword" placeholder="Password">
            <select id="newUserRole">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn">Cancel</button>
            <button class="save-btn" id="saveUserBtn">Save User</button>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script>
// Pass PHP data to JavaScript
const monthlyLabels = <?php echo json_encode($monthly_labels); ?>;
const monthlyListings = <?php echo json_encode($monthly_listings); ?>;
const monthlyUsers = <?php echo json_encode($monthly_users); ?>;
const categoryNames = <?php echo json_encode($categories); ?>;
const categoryCounts = <?php echo json_encode($category_counts); ?>;
const userGrowthLabels = <?php echo json_encode($user_growth_labels); ?>;
const userGrowthData = <?php echo json_encode($user_growth_data); ?>;
const popularCategories = <?php echo json_encode($popular_categories); ?>;
const popularCounts = <?php echo json_encode($popular_counts); ?>;
</script>

<script src="js/admintest.js"></script>