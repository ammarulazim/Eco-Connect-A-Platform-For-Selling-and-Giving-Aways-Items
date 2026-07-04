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
if (empty($item['item_image']) || !file_exists($item_image_path)) {
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

// --- Dynamic Wishlist Form Processing Engine ---
if (isset($_POST['wishlist_btn'])) {
    // Scenario A: User is logged in -> Save item to their database collection records
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        mysqli_query(
            $conn,
            "INSERT IGNORE INTO wishlist(user_id, item_id)
             VALUES('$user_id', '$item_id')"
        );
        header("Location: itemdetail.php?id=" . $item_id);
        exit();
    } 
    // Scenario B: User is a guest -> Route them straight to the authentication portal!
    else {
        header("Location: auth.php");
        exit();
    }
}

// 📌 PLACE THE REPORT COUNT CHECK RIGHT HERE
$report_count = 0;
if (isset($_SESSION['user_id'])) {
    $report_check = mysqli_query($conn, 
        "SELECT COUNT(*) as total FROM reports 
         WHERE reported_item_id = '$item_id' 
         AND status = 'pending'"
    );
    $report_data = mysqli_fetch_assoc($report_check);
    $report_count = (int)$report_data['total'];
}

$page_title = htmlspecialchars($item['item_name']) . " | Eco-Connect";
$page_css = "itemdetail.css";
include 'header.php';
?>

<main class="detail-wrapper">
    <div class="product-showcase-grid">
        
        <div class="showcase-left-stack">
            
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id'] && $report_count >= 3): ?>
                <-- Display a warning if the item has been flagged more than 3 times --!>
                <div class="owner-report-warning">
                    <div class="warning-header">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <h4>Listing Under Review (<?php echo $report_count; ?> Flags)</h4>
                    </div>
                    <p>Multiple members of the community have flagged this item. An administrator is currently reviewing your listing details to ensure compliance with our platform guidelines. Your listing remains active during this process.</p>
                </div>
            <?php endif; ?>

            <div class="product-media-stage" style="cursor: pointer;" id="triggerLightbox">
                <div class="image-wrapper">
                    <img src="<?php echo $item_image_path; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" id="mainProductImage">
                </div>
                <span class="type-ribbon <?php echo ($item['is_free'] == 1) ? 'ribbon-giveaway' : 'ribbon-premium'; ?>">
                    <?php echo ($item['is_free'] == 1) ? '<i class="fa-solid fa-gift"></i> Giveaway' : '<i class="fa-solid fa-tags"></i> For Sale'; ?>
                </span>
            </div>

            <div class="product-description-container">
                <h3 class="section-subtitle">Item Details & Condition</h3>
                <p class="description-text"><?php echo nl2br(htmlspecialchars($item['item_description'])); ?></p>
            </div>

        </div>

        <div class="product-essential-panel">
            
            <div class="panel-header">
                <div class="meta-badge-row">
                    <span class="category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                    <span class="stock-badge">
                        <?php if (isset($item['status']) && strtolower($item['status']) === 'sold'): ?>
                            <span class="pulse-dot dot-sold"></span> Sold Out
                        <?php else: ?>
                            <span class="pulse-dot dot-active"></span> Active Listing
                        <?php endif; ?>
                    </span>
                </div>
                
                <h1 class="product-title"><?php echo htmlspecialchars($item['item_name']); ?></h1>
                
                <div class="geo-timestamp-row">
                    <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($item['location']); ?></span>
                    <span class="divider-dot">•</span>
                    <span><i class="fa-regular fa-clock"></i> Verified Listing</span>
                </div>

                <div class="price-display-wrapper">
                    <span class="currency-tag">RM</span>
                    <span class="price-amount"><?php echo ($item['is_free'] == 1) ? '0.00' : number_format($item['item_price'], 2); ?></span>
                    <?php if($item['is_free'] == 1): ?>
                        <span class="free-pill">100% Free</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="seller-profile-row">
                <img src="<?php 
                    if (empty($item['profile_image']) || $item['profile_image'] === 'default_pic.jpg') {
                        echo 'images/profile/default_pic.jpg';
                    } else {
                        echo 'images/uploads/' . htmlspecialchars($owner_username) . '/' . htmlspecialchars($item['profile_image']);
                    }
                ?>" alt="Seller Avatar" class="seller-avatar">
                <div class="seller-name-meta">
                    <p class="role-title">Community Contributor</p>
                    <h4 class="username-display">@<?php echo htmlspecialchars($owner_username); ?></h4>
                </div>
            </div>

            <div class="action-footer-cluster">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
                    <a href="profile.php#my-listings" class="btn-checkout btn-owner-manage">
                        <i class="fa-solid fa-sliders"></i> Open Listing Manager
                    </a>
                <?php else: ?>
                    <div class="action-buttons-group">
                        <form method="POST" class="wishlist-form-container">
                            <button type="submit" name="wishlist_btn" class="btn-checkout btn-wishlist <?php echo ($is_wishlisted) ? 'active' : ''; ?>">
                                <?php if($is_wishlisted): ?>
                                    <i class="fa-solid fa-heart"></i> Saved
                                <?php else: ?>
                                    <i class="fa-regular fa-heart"></i> Wishlist
                                <?php endif; ?>
                            </button>
                        </form>
                        
                        <form action="messaging_order.php" method="POST" class="chat-now-form" style="display: inline;">
                            <input type="hidden" name="buyer_intent" value="1">
                            
                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                            <input type="hidden" name="meeting_location" value="To Be Arranged via Chat">
                            
                            <button type="submit" class="btn-checkout btn-buyer-contact">
                                <i class="fa-solid fa-comment-dots"></i> Chat Now
                            </button>
                        </form>
                    </div>

                    <button onclick="openReportModal()" class="btn-report-flag">
                        <i class='bx bx-error-alt'></i> Report Inappropriate Listing
                    </button>


                    <div id="reportModalOverlay" class="report-modal-overlay">
                        <div class="report-modal-card">
                            <h3><i class='bx bx-flag' style='color: #eb5e55;'></i> Report This Listing</h3>
                            
                            <form action="submit_report.php" method="POST">
                                <input type="hidden" name="report_type" value="item">
                                <input type="hidden" name="reported_item_id" value="<?php echo $item_id; ?>"> 
                                
                                <div class="report-form-group">
                                    <label>Reason for Flagging:</label>
                                    <select name="reason" class="report-form-input" required>
                                        <option value="Inappropriate Content">Inappropriate Content</option>
                                        <option value="Prohibited or Illegal Item">Prohibited/Illegal Item</option>
                                        <option value="Scam / Fake Listing">Scam/Fake Listing</option>
                                        <option value="Wrong Category Assignment">Wrong Category</option>
                                    </select>
                                </div>
                                
                                <div class="report-form-group">
                                    <label>Additional Context (Optional):</label>
                                    <textarea name="details" class="report-form-input" rows="4" placeholder="Provide extra details to help administration review this..."></textarea>
                                </div>
                                
                                <div class="report-modal-actions">
                                    <button type="button" onclick="closeReportModal()" class="btn-report-dismiss">Cancel</button>
                                    <button type="submit" name="submit_report_btn" class="btn-report-submit">Report</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="guarantee-footer-note">
                    <p><i class="fa-solid fa-handshake-angle"></i> Always meet safely in well-lit public spaces.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<div id="imageLightbox" class="lightbox-modal">
    <span class="lightbox-close">&times;</span>
    <img class="lightbox-content" id="fullResolutionImage" alt="Fullscreen View">
</div>

<script>
// --- Eco-Connect Full-Resolution Lightbox Engine ---
document.addEventListener('DOMContentLoaded', function() {
    const stage = document.getElementById('triggerLightbox');
    const modal = document.getElementById('imageLightbox');
    const fullImg = document.getElementById('fullResolutionImage');
    const closeBtn = document.querySelector('.lightbox-close');

    if (stage && modal && fullImg) {
        // 1. Open Modal when clicking the image area
        stage.addEventListener('click', function(e) {
            const targetImg = this.querySelector('#mainProductImage');
            if (targetImg) {
                modal.style.display = "flex"; // Force Flexbox layout for clean centering
                fullImg.src = targetImg.src;
                document.body.style.overflow = "hidden"; // Stop background page scroll
            }
        });

        // 2. Close Modal when clicking the 'X' close button
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = "none";
                document.body.style.overflow = "auto";
            });
        }

        // 3. Close Modal when clicking anywhere on the dark blurred backdrop
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = "none";
                document.body.style.overflow = "auto";
            }
        });
    }
});
</script>

<script>
function openReportModal() { document.getElementById('reportModalOverlay').style.display = 'flex'; }
function closeReportModal() { document.getElementById('reportModalOverlay').style.display = 'none'; }
</script>

<?php include 'footer.php'; ?>