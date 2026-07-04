<?php
    include 'database.php';

    // Security Gate: redirect if not logged in
    if (!isset($_SESSION['username'])) {
        header("Location: auth.php");
        exit();
    }

    // Fetch up-to-date user data
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT * FROM users WHERE user_id = '$user_id'";
    $user_result = mysqli_query($conn, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);

    // Set dynamic page title
    $page_title = ucfirst($user_data['username']) . " | Eco-Connect";
    $page_css = "profile.css";
    $page_script = "script4.js";

    include 'header.php';
?>

<main class="profile-wrapper">
    <div class="profile-main-card">

        <div class="cover-photo-wrapper">
            <form method="POST" enctype="multipart/form-data">
                <img src="<?php echo (!empty($user_data['cover_pic']) && $user_data['cover_pic'] !== 'NULL') ? 'images/uploads/' . $_SESSION['username'] . '/' . $user_data['cover_pic'] : 'images/profile/default_back.jpg'; ?>" alt="Profile Background" class="cover-photo">
                
                <input type="file" 
                    name="cover_photo" 
                    id="coverUpload" 
                    accept="image/*" 
                    style="display: none;"
                    onchange="this.form.submit();">

                <label for="coverUpload" class="edit-cover-btn">
                    <i class="fa-solid fa-camera"></i>
                    Edit Cover Photo
                </label>
            </form>
        </div>

        <div class="profile-inner">    
            <div class="profile-meta-bar">
                <div class="profile-avatar-wrapper">
                    <form method="POST" enctype="multipart/form-data" id="avatarForm">
                        <input type="file" 
                            name="profile_image" 
                            id="avatarUpload" 
                            accept="image/*" 
                            style="display: none;" 
                            onchange="document.getElementById('avatarForm').submit();">
                        
                        <label for="avatarUpload" class="avatar-trigger-label">
                            <img src="<?php echo (!empty($user_data['profile_image']) && $user_data['profile_image'] !== 'default.png') ? 'images/uploads/' . $_SESSION['username'] . '/' . $user_data['profile_image'] : 'images/profile/default_pic.jpg'; ?>" 
                                alt="Profile Picture" 
                                class="profile-avatar">
                                
                            <div class="avatar-hover-overlay">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                        </label>
                    </form>
                </div>
                
                <div class="profile-user-details">
                    <h1><?php echo($user_data['username']); ?></h1>

                    <p class="user-location">
                        <i class="fa-solid fa-location-dot"></i>
                        Resident of <?php echo htmlspecialchars($user_data['location']); ?>
                    </p>

                    <p class="member-since">
                        <i class="fa-regular fa-calendar"></i>
                        Community Member Since <?php echo date('F Y', strtotime($user_data['created_at'] ?? '2026-01-01')); ?>
                    </p>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php" class="edit-profile-btn"><i class="fa-solid fa-pen"></i> Edit Profile</a>
                    <a href="logout.php" class="profile-logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
        </div>

    <div class="profile-listings-card" id="my-listings">
        <div class="profile-listings-inner">
            <div class="my-items-header">
                <h2>My Listings & Shared Items</h2>
                <a href="additem.php" class="add-item-shortcut-btn">+ List New Item</a>
            </div>

            <div class="user-items-grid">
                <?php
                $items_query = "SELECT * FROM items WHERE user_id = '$user_id' ORDER BY created_at DESC";
                $items_result = mysqli_query($conn, $items_query);

                if (mysqli_num_rows($items_result) > 0) {
                    while ($item = mysqli_fetch_assoc($items_result)) {
                        $is_sold = ($item['status'] === 'sold');
                ?>
                        <div class="user-item-card">
                            <div class="card-img-container" style="position:relative; overflow:hidden; background:#12251d; height:190px;">
                                <img src="images/uploads/<?php echo $_SESSION['username']; ?>/<?php echo $item['item_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                     style="width:100%; height:100%; object-fit:cover; filter: <?php echo $is_sold ? 'grayscale(100%) brightness(45%)' : 'none'; ?>;">
                                
                                <?php if($is_sold): ?>
                                    <div style="position:absolute; top:12px; left:12px; background:#cc4e4e; color:white; padding:3px 10px; font-size:0.75rem; font-weight:700; border-radius:4px; text-transform:uppercase;">Sold</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h3 style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                    <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                                    <span style="font-size:0.8rem; color:#9ec55e; white-space:nowrap;">
                                        <?php echo $item['is_free'] ? 'Free' : 'RM ' . number_format($item['item_price'], 2); ?>
                                    </span>
                                </h3>
                                <p><?php echo htmlspecialchars(substr($item['item_description'], 0, 85)) . (strlen($item['item_description']) > 85 ? '...' : ''); ?></p>
                                <div class="card-actions">
                                    <a href="edititem.php?id=<?php echo $item['item_id']; ?>" class="action-btn edit" title="Edit Listing Details"><i class="fa-regular fa-pen-to-square"></i></a>
                                    <a href="deleteitem.php?id=<?php echo $item['item_id']; ?>" class="action-btn delete" title="Delete Listing Permanently" onclick="return confirm('Are you sure you want to remove this listing?');"><i class="fa-regular fa-trash-can"></i></a>
                                </div>
                            </div>
                        </div>
                <?php 
                    } 
                } else { 
                ?>
                    <div class="empty-listings-placeholder">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <p>You haven't listed any items for exchange or donation yet.</p>
                        <a href="additem.php" class="accent-link">Share your first item now</a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>