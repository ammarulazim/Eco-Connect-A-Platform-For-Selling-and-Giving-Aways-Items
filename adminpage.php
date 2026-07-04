<?php
include 'database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

/* DASHBOARD STATS FETCH LOOPS */
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$item_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items"))['total'];
$active_listings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE status = 'Available'"))['total'] ?? 0;

// Gather structural time tracking loops for analytical charts
$monthly_labels = []; $monthly_listings = []; $monthly_users = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthly_labels[] = date('M Y', strtotime("-$i months"));
    $monthly_users[] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'"))['count'];
    $monthly_listings[] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM items WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'"))['count'];
}

$category_query = mysqli_query($conn, "SELECT category, COUNT(*) AS count FROM items GROUP BY category ORDER BY count DESC");
$categories = []; $category_counts = [];
while ($cat = mysqli_fetch_assoc($category_query)) {
    $categories[] = $cat['category'] ?? 'Others';
    $category_counts[] = $cat['count'];
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
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo"> 
                    <span><img class="logo1" src="images/logo/logo1.png" alt="Logo"></span>
                    <span>Eco-Connect</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active" data-tab="dashboard"><a href="#"><i class='bx bx-grid-alt'></i> <span>Dashboard</span></a></li>
                    <li class="nav-item" data-tab="users"><a href="#"><i class='bx bx-group'></i> <span>Users</span></a></li>
                    <li class="nav-item" data-tab="items"><a href="#"><i class='bx bx-package'></i> <span>Items</span></a></li>
                    <li class="nav-item" data-tab="reports"><a href="#"><i class='bx bx-error-alt'></i> <span>Reports Hub</span></a></li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class='bx bx-log-out'></i> <span>Logout</span></a>
            </div>
        </aside>

        <section class="admin-content">
            <div class="topbar">
                <div>
                    <h1 id="pageTitle">Dashboard</h1>
                    <p id="pageSubtitle">System Overview Control Panel Control</p>
                </div>
                <div class="topbar-actions">
                    <button class="theme-toggle" id="darkModeBtn">
                        <i class='bx bx-moon'></i>
                    </button>
                </div>
            </div>

            <section class="tab-content active" id="dashboardTab">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class='bx bx-user'></i></div>
                        <div class="stat-info">
                            <h3>Total Users</h3>
                            <p class="stat-number"><?php echo $user_count; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class='bx bx-package'></i></div>
                        <div class="stat-info">
                            <h3>Total Listings</h3>
                            <p class="stat-number"><?php echo $item_count; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class='bx bx-leaf'></i></div>
                        <div class="stat-info">
                            <h3>Active Listings</h3>
                            <p class="stat-number"><?php echo $active_listings; ?></p>
                        </div>
                    </div>
                </div>

                <div class="charts-row">
                    <div class="chart-card"><h3>Monthly Activity</h3><canvas id="activityChart"></canvas></div>
                    <div class="chart-card"><h3>Category Distribution</h3><canvas id="categoryChart"></canvas></div>
                </div>
            </section>

            <section class="tab-content" id="usersTab">
                <div class="table-card">
                    <div class="table-header"><h3>User Operational Accounts</h3></div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status Flag</th>
                                    <th>Actions</th>
                                </tr>
                            </thead> <tbody> <?php
                                $users_res = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
                                while($user = mysqli_fetch_assoc($users_res)) {
                                    $status = $user['status'] ?? 'Active';
                                ?>
                                <tr>
                                    <td>#<?php echo $user['user_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                                    <td>
                                        <button class="action-btn edit-btn" onclick="openUserModModal(<?php echo $user['user_id']; ?>, '<?php echo $status; ?>')"><i class='bx bx-shield-quarter'></i> Moderate</button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table> </div>
                </div>
            </section>

            <section class="tab-content" id="itemsTab">
                <div class="table-card">
                    <div class="table-header"><h3>Active System Marketplace Listings</h3></div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Owner</th>
                                    <th>Price Label</th>
                                    <th>Status Status</th>
                                    <th>Complaints Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $items_res = mysqli_query($conn, "SELECT items.*, users.username FROM items JOIN users ON items.user_id = users.user_id ORDER BY items.created_at DESC");
                                while($item = mysqli_fetch_assoc($items_res)) {
                                    $item_id = $item['item_id'];
                                    $complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM reports WHERE reported_item_id = $item_id"))['total'];
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['username']); ?></td>
                                    <td>RM <?php echo number_format($item['item_price'], 2); ?></td>
                                    <td><?php echo $item['status']; ?></td>
                                    <td><span style="color: <?php echo $complaints > 0 ? '#ff4d4d' : '#9ec55e'; ?>; font-weight: bold;"><?php echo $complaints; ?> Report(s)</span></td>
                                    <td>
                                        <a href="javascript:void(0);" 
                                           class="action-btn delete-btn" 
                                           onclick="openTakedownModal(event, '<?php echo $item_id; ?>')">
                                            <i class='bx bx-trash'></i> Takedown
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="tab-content" id="reportsTab">
                <div class="table-card">
                    <div class="table-header"><h3>Active Community Feedback & Abuse Reports</h3></div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Target Details</th>
                                    <th>Reason / Incident Note</th>
                                    <th>Reported At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $reports = mysqli_query($conn, "SELECT r.*, u.username AS reporter FROM reports r JOIN users u ON r.reporter_id = u.user_id ORDER BY r.created_at DESC");
                                if(mysqli_num_rows($reports) == 0) {
                                    echo '<tr><td colspan="7" style="text-align:center; padding: 20px; color: #8fa399;">No active community incident tickets generated yet.</td></tr>';
                                }
                                while($rep = mysqli_fetch_assoc($reports)) {
                                    $target = '';
                                    if($rep['report_type'] == 'user') {
                                        $t_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE user_id = ".intval($rep['reported_user_id'])));
                                        $target = 'User: <strong>'.htmlspecialchars($t_user['username'] ?? 'Unknown').'</strong>';
                                    } else {
                                        $t_item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT item_name FROM items WHERE item_id = ".intval($rep['reported_item_id'])));
                                        $target = 'Listing: <strong>'.htmlspecialchars($t_item['item_name'] ?? 'Removed Item').'</strong>';
                                    }
                                ?>
                                <tr>
                                    <td>#<?php echo $rep['report_id']; ?></td>
                                    <td><span class="role-badge" style="background: rgba(235, 94, 40, 0.2); color: #eb5e28;"><?php echo strtoupper($rep['report_type']); ?></span></td>
                                    <td><?php echo $target; ?> (By: <?php echo htmlspecialchars($rep['reporter']); ?>)</td>
                                    <td><em>"<?php echo htmlspecialchars($rep['reason']); ?>"</em></td>
                                    <td><?php echo date('d M, h:m A', strtotime($rep['created_at'])); ?></td>
                                    <td><span class="status-badge" style="background: <?php echo $rep['status'] == 'pending' ? '#ff4d4d' : '#9ec55e'; ?>"><?php echo ucfirst($rep['status']); ?></span></td>
                                    <td>
                                        <?php if($rep['status'] == 'pending'): ?>
                                            <a href="admin_action.php?resolve_report=<?php echo $rep['report_id']; ?>" class="action-btn edit-btn" style="background: #9ec55e; color: #000;"><i class='bx bx-check-shield'></i> Clear</a>
                                        <?php if($rep['report_type'] == 'item' && isset($rep['reported_item_id'])): ?>
                                            <a href="javascript:void(0);" class="action-btn delete-btn" style="margin-left: 5px;" onclick="openTakedownModal(event, '<?php echo $rep['reported_item_id']; ?>')"><i class='bx bx-trash'></i> Takedown</a>
                                        <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: #8fa399;">Handled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </section>
    </div>
</main>

<div id="userModModal" class="modal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); align-items:center; justify-content:center; z-index:99999;">
    <div class="modal-content" style="background:#0d1a13; padding: 25px; border-radius:12px; width:100%; max-width:450px; color:#fff; border: 1px solid rgba(255,255,255,0.1);">
        <form action="admin_action.php" method="POST">
            <input type="hidden" name="action_type" value="moderate_user">
            <input type="hidden" name="user_id" id="modModalUserId">
            
            <h3>Account Enforcement Options</h3>
            <div style="margin: 15px 0;">
                <label style="display:block; margin-bottom: 5px;">Enforcement Rule</label>
                <select name="status" id="modModalStatus" style="width:100%; padding:10px; background:#162a1f; color:#fff; border:1px solid #333; border-radius:6px;" required>
                    <option value="Active">Active (Clear All Warnings / Bans)</option>
                    <option value="Warned">Warned (Issue Administrative Citation)</option>
                    <option value="Banned">Banned (Prevent System Authentication Access)</option>
                </select>
            </div>
            <div style="margin: 15px 0;">
                <label style="display:block; margin-bottom: 5px;">Warning Citation Summary Label (Shown to user)</label>
                <input type="text" name="warning_message" id="modModalMsg" placeholder="e.g., Suspicious marketplace trade activity..." style="width:100%; padding:10px; background:#162a1f; color:#fff; border:1px solid #333; border-radius:6px;">
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" onclick="closeUserModModal()" style="padding:10px 15px; background:#333; color:#fff; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:10px 15px; background:#9ec55e; color:#000; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">Apply Rules</button>
            </div>
        </form>
    </div>
</div>

<div id="takedownModal" class="takedown-modal-overlay" style="display:none;">
    <div class="takedown-modal-card">
        <div class="modal-icon-header">
            <i class='bx bx-error-alt'></i>
        </div>
        <h3>Confirm Takedown</h3>
        <p>Are you sure you want to permanently delete this item from the Eco-Connect marketplace? This manual action cannot be undone.</p>
        
        <div class="modal-actions-cluster">
            <button class="modal-btn btn-cancel" onclick="closeTakedownModal()">Cancel</button>
            <a id="modalConfirmDeleteLink" href="#" class="modal-btn btn-confirm-delete">Delete Permanently</a>
        </div>
    </div>
</div>

<script>
const monthlyLabels = <?php echo json_encode($monthly_labels); ?>;
const monthlyListings = <?php echo json_encode($monthly_listings); ?>;
const monthlyUsers = <?php echo json_encode($monthly_users); ?>;
const categoryNames = <?php echo json_encode($categories); ?>;
const categoryCounts = <?php echo json_encode($category_counts); ?>;

function openUserModModal(userId, currentStatus) {
    document.getElementById('modModalUserId').value = userId;
    document.getElementById('modModalStatus').value = currentStatus;
    document.getElementById('userModModal').style.display = 'flex';
}
function closeUserModModal() {
    document.getElementById('userModModal').style.display = 'none';
}

/* ✅ UPDATED INTERACTION SCRIPTS */
function openTakedownModal(event, itemId) {
    // Kill bubbling out of native system actions loop
    event.stopPropagation();
    event.preventDefault();
    
    const confirmLink = document.getElementById('modalConfirmDeleteLink');
    confirmLink.href = `admin_takedown.php?id=${itemId}`;
    
    // Set to flex layout to handle look presentation properties cleanly
    document.getElementById('takedownModal').style.display = 'flex';
    setTimeout(() => {
        document.getElementById('takedownModal').classList.add('active');
    }, 10);
}

function closeTakedownModal() {
    document.getElementById('takedownModal').classList.remove('active');
    setTimeout(() => {
        document.getElementById('takedownModal').style.display = 'none';
    }, 300);
}

window.onclick = function(event) {
    const modal = document.getElementById('takedownModal');
    if (event.target === modal) {
        closeTakedownModal();
    }
}
</script>
<script src="js/admintest.js"></script>