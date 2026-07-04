<?php
include 'database.php'; // Initializes session and establishes DB connection

// 1. Validate that an item ID was actually passed in the URL string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$item_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Fetch the item details along with the owner's username and profile info
$query = "SELECT items.*, users.username, users.profile_image 
          FROM items 
          JOIN users ON items.user_id = users.user_id 
          WHERE items.item_id = '$item_id'";

$result = mysqli_query($conn, $query);

// If the item doesn't exist, kick back to the homepage
if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit();
}

$item = mysqli_fetch_assoc($result);

// 3. Determine dynamic folder path based on owner's username
$owner_username = $item['username'];
$item_image_path = "images/uploads/" . $owner_username . "/" . $item['item_image'];

// Fallback checking if file disappears or doesn't exist
if (!file_exists($item_image_path) || empty($item['item_image'])) {
    $item_image_path = "images/items/default_item.png"; 
}

$page_title = htmlspecialchars($item['item_name']) . " | Eco-Connect";
$page_css = "itemdetail.css";
include 'header.php';
?>

<main class="detail-wrapper">
    <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="separator">/</span>
        <span class="current"><?php echo htmlspecialchars($item['item_name']); ?></span>
    </div>

    <div class="detail-container">
        <div class="detail-image-box">
            <img src="<?php echo $item_image_path; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
            <span class="status-badge <?php echo ($item['is_free'] == 1) ? 'badge-free' : 'badge-sell'; ?>">
                <?php echo ($item['is_free'] == 1) ? 'Giveaway' : 'For Sale'; ?>
            </span>
        </div>

        <div class="detail-info-box">
            <div class="info-header">
                <span class="category-tag">📦 <?php echo htmlspecialchars($item['category']); ?></span>
                <h1><?php echo htmlspecialchars($item['item_name']); ?></h1>
                
                <div class="price-tag">
                    <?php echo ($item['is_free'] == 1) ? 'FREE' : 'RM ' . number_format($item['item_price'], 2); ?>
                </div>
                
                <div class="location-tag">
                    <i class="fa-solid fa-location-dot"></i> Listed in <?php echo htmlspecialchars($item['location']); ?>
                </div>
            </div>

            <hr class="divider">

            <div class="owner-card">
                <!-- <img src="images/uploads/<?php echo !empty($item['profile_image']) ? $item['profile_image'] : 'default.png'; ?>" alt="Owner avatar"> -->
                 <img src="<?php 
                                // Check if the user is still using the default registration image
                                if (empty($item['profile_image']) || $item['profile_image'] === 'default_pic.jpg') {
                                    // Point to your global default assets folder
                                    echo 'images/profile/default_pic.jpg';
                                } else {
                                    // Point to the user's custom uploads folder where their unique avatar is stored
                                    echo 'images/uploads/' . htmlspecialchars($owner_username) . '/' . htmlspecialchars($item['profile_image']);
                                }
                            ?>" alt="Owner avatar">
                <div class="owner-meta">
                    <span class="label">Listed By</span>
                    <span class="name">@<?php echo htmlspecialchars($owner_username); ?></span>
                </div>
            </div>

            <div class="info-body">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($item['item_description'])); ?></p>
            </div>

            <div class="info-footer">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
                    <a href="profile.php#my-listings" class="btn-action btn-manage">
                        <i class="fa-solid fa-pen-to-square"></i> Manage Your Listing
                    </a>
                <?php else: ?>
                    <!--
                    <a href="mailto:contact@eco-connect.com?subject=Inquiry about <?php echo urlencode($item['item_name']); ?>" class="btn-action btn-contact">
                        <i class="fa-regular fa-comments"></i> Contact @<?php echo htmlspecialchars($owner_username); ?>
                    </a>
                    -->
                    <a href="messaging.php?to_id=<?php echo $item['user_id']; ?>&item=<?php echo urlencode($item['item_name']); ?>" class="btn-action btn-contact">
                        <i class="fa-regular fa-comments"></i> Contact @<?php echo htmlspecialchars($owner_username); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>