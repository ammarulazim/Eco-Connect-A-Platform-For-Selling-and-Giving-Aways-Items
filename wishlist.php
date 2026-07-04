<?php
include 'database.php';

// Route guard: Redirect to login if user isn't authenticated
if (!isset($_SESSION['user_id'])) {
    // If native header fails due to earlier invisible characters, JavaScript catches it instantly
    echo "<script type='text/javascript'>window.location.href = 'auth.php';</script>";
    exit();
}

$current_user_id = $_SESSION['user_id'];

$page_title = "My Wishlist";
$page_css = "wishlist.css";

include 'header.php';

$query = "
SELECT items.*, users.username
FROM wishlist
JOIN items ON wishlist.item_id = items.item_id
JOIN users ON items.user_id = users.user_id
WHERE wishlist.user_id = '$current_user_id'
ORDER BY wishlist.created_at DESC
";

$result = mysqli_query($conn, $query);
?>

<main class="wishlist-wrapper">

    <h1>My Wishlist</h1>

    <div class="wishlist-grid">

        <?php if(mysqli_num_rows($result) > 0): ?>

            <?php while($item = mysqli_fetch_assoc($result)): ?>

                <div class="user-item-card" id="wishlist-item-<?php echo $item['item_id']; ?>">
                    <div class="card-img-container">
                        <a href="itemdetail.php?id=<?php echo $item['item_id']; ?>">
                            <img 
                                src="images/uploads/<?php echo $item['username']; ?>/<?php echo $item['item_image']; ?>" 
                                alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                            >
                        </a>
                    </div>

                    <div class="card-body">
                        <h3>
                            <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                            <span class="item-price-tag">
                                <?php echo $item['is_free'] ? 'Free' : 'RM ' . number_format($item['item_price'], 2); ?>
                            </span>
                        </h3>
                        
                        <p><?php echo htmlspecialchars(substr($item['item_description'] ?? '', 0, 85)) . (strlen($item['item_description'] ?? '') > 85 ? '...' : ''); ?></p>
                        
                        <div class="card-actions">
                            <a href="itemdetail.php?id=<?php echo $item['item_id']; ?>" class="action-btn view" title="View Item Details">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               class="action-btn delete" 
                               title="Remove from Wishlist" 
                               onclick="removeFromWishlist(event, <?php echo $item['item_id']; ?>);">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="empty-state-text" id="emptyMessage">You haven't added anything to your wishlist yet.</p>

        <?php endif; ?>

    </div>

</main>

<script>
function removeFromWishlist(event, itemId) {
    event.preventDefault(); // Lock down traditional anchor tracking routes
    
    if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
        return;
    }

    // Fire off async background query task pipelines targeting processing endpoint
    fetch(`remove_wishlist.php?id=${itemId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const targetCard = document.getElementById(`wishlist-item-${itemId}`);
            if (targetCard) {
                // Apply a smooth visual transition fade out
                targetCard.style.opacity = '0';
                targetCard.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    targetCard.remove();
                    
                    // Dynamic fallback check: If no elements remain, display empty placeholder text
                    const remainingCards = document.querySelectorAll('.user-item-card');
                    if (remainingCards.length === 0) {
                        const grid = document.querySelector('.wishlist-grid');
                        grid.innerHTML = '<p class="empty-state-text">You haven\'t added anything to your wishlist yet.</p>';
                    }
                }, 300); // Matches CSS fade sequence timeline limits
            }
        } else {
            alert(data.error || 'Failed to remove item. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

<?php include 'footer.php'; ?>