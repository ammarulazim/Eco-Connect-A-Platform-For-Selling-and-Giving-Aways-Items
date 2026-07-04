<?php
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Automatically mark notifications as read when page opens
mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");

$sql = "SELECT * FROM notifications 
        WHERE user_id = '$user_id' 
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

// Setup page variables to pull through header layout links cleanly
$page_title = "Notifications | Eco-Connect";
$page_css = "notifications.css"; 
include 'header.php';
?>

<main class="notifications-container">
    <h2>Notifications</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>

            <?php
                // Redirect logic context maps for message and system activities
                if ($row['type'] == 'message') {
                    $link = "messaging.php?to_id=" . $row['related_id'];
                    $icon = '<i class="fa-solid fa-comment-dots" style="color: #9ec55e; margin-right: 8px;"></i>';
                } else {
                    $link = "#";
                    $icon = '<i class="fa-solid fa-circle-info" style="color: #8fa399; margin-right: 8px;"></i>';
                }
            ?>

            <a href="<?php echo $link; ?>" class="notification-card">
                <span class="notification-text">
                    <?php echo $icon; ?>
                    <?php echo htmlspecialchars($row['message']); ?>
                </span>
                <small class="notification-meta">
                    <i class="fa-regular fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                </small>
            </a>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-notifications-state">
            <i class="fa-regular fa-bell-slash"></i>
            <p>Your notification tray is completely clear right now.</p>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>