<?php
include 'database.php';

// SECURITY CHECK: If the user is not logged in OR they are not an admin, boot them out!
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$page_title = "Admin Dashboard | Eco-Connect";
$page_css = "admin.css"; 

include 'header.php';
?>

<main class="profile-wrapper">
    <div class="profile-main-card" style="background: #112219; padding: 40px 0; text-align: center;">
        <div class="profile-inner" style="margin: 0 auto; max-width: 800px;">
            
            <div style="font-size: 4rem; color: #9ec55e; margin-bottom: 15px;">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            
            <h1 style="font-size: 2.8rem; font-weight: 800; margin: 0 0 10px 0; color: #ffffff;">
                Admin Control Dashboard
            </h1>
            
            <p style="color: #a8b5ae; font-size: 1.1rem; margin-bottom: 30px;">
                Welcome back, Admin <strong style="color: #9ec55e;"><?php echo ucfirst($_SESSION['username']); ?></strong>. Management utilities are secured.
            </p>

            <div style="display: flex; justify-content: center; gap: 15px;">
                <a href="logout.php" class="profile-logout-btn" style="padding: 12px 35px; font-size: 1rem; cursor: pointer;">
                    <i class="fa-solid fa-right-from-bracket"></i> Secure Logout
                </a>
            </div>

        </div>
    </div>

    <div class="profile-listings-card">

        <div class="profile-listings-inner">

            <!-- DASHBOARD STATS -->
            <div class="admin-stats-grid">

                <?php
                    $users_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
                    $items_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM items"));
                ?>

                <div class="admin-stat-card">
                    <i class="fa-solid fa-users"></i>
                    <h2><?php echo $users_count; ?></h2>
                    <p>Total Users</p>
                </div>

                <div class="admin-stat-card">
                    <i class="fa-solid fa-box-open"></i>
                    <h2><?php echo $items_count; ?></h2>
                    <p>Total Listings</p>
                </div>

            </div>

            <!-- USERS MANAGEMENT -->
            <div class="admin-section">

                <div class="admin-section-header">
                    <h2>Manage Users</h2>
                </div>

                <div class="admin-table-wrapper">

                    <table class="admin-table">

                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>

                        <?php

                        $users_query = "SELECT * FROM users ORDER BY user_id DESC";
                        $users_result = mysqli_query($conn, $users_query);

                        while($user = mysqli_fetch_assoc($users_result)) {

                        ?>

                        <tr>
                            <td><?php echo $user['user_id']; ?></td>

                            <td>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user['location']); ?>
                            </td>

                            <td>
                                <?php echo ucfirst($user['role']); ?>
                            </td>

                            <td class="admin-actions">

                                <a href="delete_user.php?id=<?php echo $user['user_id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this user?')">

                                    <i class="fa-solid fa-trash"></i>
                                </a>

                            </td>
                        </tr>

                        <?php } ?>

                    </table>

                </div>

            </div>

            <!-- LISTINGS MANAGEMENT -->
            <div class="admin-section">

                <div class="admin-section-header">
                    <h2>Manage Listings</h2>
                </div>

                <div class="admin-table-wrapper">

                    <table class="admin-table">

                        <tr>
                            <th>ID</th>
                            <th>Item</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>

                        <?php

                        $items_query = "
                            SELECT items.*, users.username
                            FROM items
                            JOIN users ON items.user_id = users.user_id
                            ORDER BY items.item_id DESC
                        ";

                        $items_result = mysqli_query($conn, $items_query);

                        while($item = mysqli_fetch_assoc($items_result)) {

                        ?>

                        <tr>

                            <td><?php echo $item['item_id']; ?></td>

                            <td>
                                <?php echo htmlspecialchars($item['item_name']); ?>
                            </td>

                            <td>
                                @<?php echo htmlspecialchars($item['username']); ?>
                            </td>

                            <td>
                                <?php echo ucfirst($item['status']); ?>
                            </td>

                            <td class="admin-actions">

                                <a href="delete_item.php?id=<?php echo $item['item_id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this listing?')">

                                    <i class="fa-solid fa-trash"></i>
                                </a>

                            </td>

                        </tr>

                        <?php } ?>

                    </table>

                </div>

            </div>

        </div>

    </div>
</main>

<?php include 'footer.php'; ?>