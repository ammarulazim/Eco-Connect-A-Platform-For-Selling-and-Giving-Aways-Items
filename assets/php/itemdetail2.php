<?php
include 'database.php'; // Initializes session and establishes DB connection

// 1. Validate that an item ID was actually passed in the URL string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$item_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Fetch item details with optimized table joins for account attributes
$query = "SELECT items.*, users.username, users.profile_image 
          FROM items 
          JOIN users ON items.user_id = users.user_id 
          WHERE items.item_id = '$item_id'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit();
}

$item = mysqli_fetch_assoc($result);

$owner_username = $item['username'];
$item_image_path = "images/uploads/" . $owner_username . "/" . $item['item_image'];

// Fallback checking if file does not exist in local directory paths
if (!file_exists($item_image_path) || empty($item['item_image'])) {
    $item_image_path = "images/items/default_item.png"; 
}

$is_wishlisted = false;

if (isset($_SESSION['user_id'])) {

    $wishlist_check = mysqli_query(
        $conn,
        "SELECT * FROM wishlist
         WHERE user_id='{$_SESSION['user_id']}'
         AND item_id='$item_id'"
    );

    $is_wishlisted = mysqli_num_rows($wishlist_check) > 0;
}

if (
    isset($_SESSION['user_id']) &&
    isset($_POST['wishlist_btn'])
) {

    $user_id = $_SESSION['user_id'];

    mysqli_query(
        $conn,
        "INSERT IGNORE INTO wishlist(user_id,item_id)
         VALUES('$user_id','$item_id')"
    );

    header("Location: itemdetail.php?id=".$item_id);
    exit();
}

$page_title = htmlspecialchars($item['item_name']) . " | Eco-Connect";
$page_css = "itemdetail.css";
include 'header.php';
?>

<main class="detail-wrapper">
    <div class="breadcrumb-nav">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <i class="fa-solid fa-chevron-right separator"></i>
        <span class="category-path"><?php echo htmlspecialchars($item['category']); ?></span>
        <i class="fa-solid fa-chevron-right separator"></i>
        <span class="current-product"><?php echo htmlspecialchars($item['item_name']); ?></span>
    </div>

    <div class="product-showcase-grid">
        
        <div class="product-media-stage">
            <div class="image-wrapper">
                <img src="<?php echo $item_image_path; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" id="mainProductImage">
            </div>
            <span class="type-ribbon <?php echo ($item['is_free'] == 1) ? 'ribbon-giveaway' : 'ribbon-premium'; ?>">
                <?php echo ($item['is_free'] == 1) ? '<i class="fa-solid fa-gift"></i> Giveaway' : '<i class="fa-solid fa-tags"></i> For Sale'; ?>
            </span>
        </div>

        <div class="product-essential-panel">
            <div class="panel-header">
                <div class="meta-badge-row">
                    <span class="category-badge"><i class="fa-regular fa-folder-open"></i> <?php echo htmlspecialchars($item['category']); ?></span>
                    <span class="stock-badge"><i class="fa-solid fa-circle-check"></i> Available</span>
                </div>
                
                <h1 class="product-title"><?php echo htmlspecialchars($item['item_name']); ?></h1>
                
                <div class="price-display-wrapper">
                    <span class="currency-tag">RM</span>
                    <span class="price-amount"><?php echo ($item['is_free'] == 1) ? '0.00' : number_format($item['item_price'], 2); ?></span>
                    <?php if($item['is_free'] == 1): ?>
                        <!-- <span class="free-pill">100% FREE</span> -->
                        <span class="free-pill">Giveaway</span>
                    <?php endif; ?>
                </div>

                <div class="geo-timestamp-row">
                    <span class="geo-tag"><i class="fa-solid fa-location-dot"></i> Puchong, <?php echo htmlspecialchars($item['location']); ?></span>
                    <span class="time-tag"><i class="fa-regular fa-clock"></i> Listed recently</span>
                </div>
            </div>

            <div class="seller-trust-card">
                <div class="seller-info">
                    <img src="<?php 
                        if (empty($item['profile_image']) || $item['profile_image'] === 'default_pic.jpg') {
                            echo 'images/profile/default_pic.jpg';
                        } else {
                            echo 'images/uploads/' . htmlspecialchars($owner_username) . '/' . htmlspecialchars($item['profile_image']);
                        }
                    ?>" alt="Seller Avatar" class="seller-avatar">
                    <div class="seller-name-meta">
                        <p class="role-title">Verified Community Contributor</p>
                        <h4 class="username-display">@<?php echo htmlspecialchars($owner_username); ?></h4>
                    </div>
                </div>
                <div class="trust-badge-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <div class="product-description-container">
                <h3 class="section-subtitle">Item Details & Condition</h3>
                <p class="description-text"><?php echo nl2br(htmlspecialchars($item['item_description'])); ?></p>
            </div>

            <div class="action-footer-cluster">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
                    <a href="profile.php#my-listings" class="btn-checkout btn-owner-manage">
                        <i class="fa-solid fa-sliders"></i> Manage Dashboard Listing
                    </a>
                <?php else: ?>
                    
                    <form method="POST">
                        <button type="submit"
                                name="wishlist_btn"
                                class="btn-checkout btn-owner-manage">

                            <?php if($is_wishlisted): ?>
                                <i class="fa-solid fa-heart"></i> Added To Wishlist
                            <?php else: ?>
                                <i class="fa-regular fa-heart"></i> Add To Wishlist
                            <?php endif; ?>

                        </button>
                    </form>
                    <!--
                    <a href="messaging.php?to_id=<?php echo $item['user_id']; ?>&item=<?php echo urlencode($item['item_name']); ?>" class="btn-checkout btn-buyer-contact">
                        <i class="fa-solid fa-comment-dots"></i> Chat Now
                    </a>
                    -->

                    <!--
                    <a href="messaging.php "class="btn-checkout btn-buyer-contact">
                        <i class="fa-solid fa-comment-dots"></i> Chat Now
                    </a>
                    -->
                    
                    <a href="messaging.php?to_id=<?php echo $item['user_id']; ?>&item=<?php echo urlencode($item['item_name']); ?>" 
                        class="btn-checkout btn-buyer-contact">
                            <i class="fa-solid fa-comment-dots"></i> Chat Now
                    </a>
                <?php endif; ?>
                
                <div class="guarantee-footer-note">
                    <p><i class="fa-solid fa-handshake-angle"></i> Meet up safely in public spaces within local communities.</p>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include 'footer.php'; ?>