<?php
include 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Security check: Verify item ownership
$check_query = "SELECT * FROM items WHERE item_id = '$item_id' AND user_id = '$user_id'";
$check_result = mysqli_query($conn, $check_query);
$item = mysqli_fetch_assoc($check_result);

if (!$item) {
    header("Location: profile.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $item_description = mysqli_real_escape_string($conn, $_POST['item_description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $item_price = (!$is_free && isset($_POST['item_price'])) ? floatval($_POST['item_price']) : 0.00;
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    $file_name = $item['item_image'];
    
    if (!empty($_FILES["item_image"]["name"])) {
        $target_dir = "uploads/" . $username . "/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["item_image"]["name"]);
        move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_dir . $file_name);
    }
    
    $update_query = "UPDATE items SET 
                        item_name = '$item_name', 
                        item_description = '$item_description', 
                        category = '$category',
                        item_price = '$item_price', 
                        is_free = '$is_free', 
                        item_image = '$file_name', 
                        location = '$location',
                        status = '$status' 
                     WHERE item_id = '$item_id'";
                     
    if (mysqli_query($conn, $update_query)) {
        // Redirect with animation flag
        header("Location: profile.php#my-listings?scroll=animated");
        exit();
    }
}

$page_title = "Edit Listing | Eco-Connect";
$page_css = "listingform.css";
include 'header.php';

// Determine image path
$image_path = "uploads/" . $username . "/" . $item['item_image'];
if (!file_exists($image_path)) {
    $image_path = "images/uploads/" . $username . "/" . $item['item_image'];
    if (!file_exists($image_path)) {
        $image_path = "placeholder-image.jpg";
    }
}
?>

<main class="profile-wrapper">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="separator">/</span>
        <a href="profile.php#my-listings" class="breadcrumb-link">My Items</a>
        <span class="separator">/</span>
        <span class="current">Edit Listing</span>
    </div>

    <div class="profile-main-card">
        <div class="card-header">
            <h2>
                <i class="fa-regular fa-pen-to-square"></i>
                Edit Item Listing
            </h2>
            <p>Update your item details to attract more potential exchangers</p>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <!-- Status Field -->
                <div class="form-group">
                    <label>Availability Status <span class="required">*</span></label>
                    <select name="status" required>
                        <option value="available" <?php echo $item['status'] === 'available' ? 'selected' : ''; ?>>✅ Available For Exchange</option>
                        <option value="sold" <?php echo $item['status'] === 'sold' ? 'selected' : ''; ?>>❌ Marked as Sold / Gifted</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="Electronics" <?php echo ($item['category'] ?? '') == 'Electronics' ? 'selected' : ''; ?>>📱 Electronics</option>
                        <option value="Furniture" <?php echo ($item['category'] ?? '') == 'Furniture' ? 'selected' : ''; ?>>🪑 Furniture</option>
                        <option value="Clothing" <?php echo ($item['category'] ?? '') == 'Clothing' ? 'selected' : ''; ?>>👕 Clothing</option>
                        <option value="Sports" <?php echo ($item['category'] ?? '') == 'Sports' ? 'selected' : ''; ?>>⚽ Sports</option>
                        <option value="Books" <?php echo ($item['category'] ?? '') == 'Books' ? 'selected' : ''; ?>>📚 Books</option>
                        <option value="Others" <?php echo ($item['category'] ?? '') == 'Others' ? 'selected' : ''; ?>>📦 Others</option>
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <label>Item Name <span class="required">*</span></label>
                    <input type="text" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required placeholder="e.g., Samsung Galaxy S21, Wooden Dining Table">
                </div>

                <div class="form-group form-group-full">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="item_description" rows="4" required placeholder="Describe your item condition, features, and any important details..."><?php echo htmlspecialchars($item['item_description']); ?></textarea>
                    <div class="form-hint">Be specific about the condition, age, and any defects</div>
                </div>

                <div class="form-group form-group-full">
                    <div class="price-section">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="isFreeCheckbox" name="is_free" <?php echo $item['is_free'] ? 'checked' : ''; ?> onchange="togglePriceField()">
                            <label for="isFreeCheckbox">🎁 This item is free (Donation)</label>
                        </div>
                        
                        <div id="priceInputWrapper" style="display: <?php echo $item['is_free'] ? 'none' : 'block'; ?>; margin-top: 1rem;">
                            <label>Asking Price (RM)</label>
                            <input type="number" step="0.01" min="0" name="item_price" id="itemPriceField" value="<?php echo $item['item_price']; ?>" placeholder="0.00">
                            <div class="form-hint">Suggest a fair market price</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Exchange Location <span class="required">*</span></label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($item['location']); ?>" required placeholder="e.g., Puchong, Kuala Lumpur">
                    <div class="form-hint">📍 Where can the buyer pick up this item?</div>
                </div>

                <div class="form-group">
                    <label>Item Image</label>
                    <div class="file-upload" onclick="document.getElementById('itemImage').click()">
                        <div class="file-upload-label">
                            <i class="fa-solid fa-cloud-upload-alt"></i>
                            <span>Click to change image</span>
                            <span class="small">PNG, JPG up to 5MB</span>
                        </div>
                        <input type="file" id="itemImage" name="item_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                    </div>
                    
                    <div class="current-image-preview">
                        <div class="image-preview-container">
                            <div class="image-preview">
                                <img id="currentImagePreview" src="<?php echo $image_path; ?>" alt="Current item image">
                            </div>
                            <div class="image-info">
                                <p><i class="fa-regular fa-image"></i> Current image</p>
                                <p><i class="fa-regular fa-file"></i> <?php echo htmlspecialchars($item['item_image']); ?></p>
                                <p><i class="fa-regular fa-clock"></i> Uploaded when item was created</p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="newImagePreview" style="display: none; margin-top: var(--spacing-md); padding: var(--spacing-md); background: rgba(158, 197, 94, 0.1); border-radius: var(--radius-lg); border: 1px solid var(--secondary-500);">
                        <div class="image-preview-container">
                            <div class="image-preview">
                                <img id="newImageView" src="" alt="New image preview">
                            </div>
                            <div class="image-info">
                                <p><i class="fa-regular fa-spinner"></i> New image will replace current</p>
                                <p id="newImageName"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">
                    <i class="fa-regular fa-floppy-disk"></i> Save Changes
                </button>
                <a href="profile.php#my-listings" class="btn-secondary cancel-link">
                    <i class="fa-regular fa-circle-xmark"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<script>
function togglePriceField() {
    const isFree = document.getElementById('isFreeCheckbox').checked;
    const wrapper = document.getElementById('priceInputWrapper');
    const field = document.getElementById('itemPriceField');
    if (isFree) {
        wrapper.style.display = 'none';
        field.value = '0';
    } else {
        wrapper.style.display = 'block';
    }
}

function previewImage(input) {
    const newPreviewDiv = document.getElementById('newImagePreview');
    const newImageView = document.getElementById('newImageView');
    const newImageName = document.getElementById('newImageName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            newImageView.src = e.target.result;
            newPreviewDiv.style.display = 'block';
            newImageName.innerHTML = '<i class="fa-regular fa-file"></i> ' + input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Smooth scroll animation for breadcrumb and cancel button
document.querySelectorAll('.breadcrumb-link, .cancel-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetUrl = this.getAttribute('href');
        const targetHash = targetUrl.split('#')[1];
        
        // Store in session that we need to scroll
        sessionStorage.setItem('scrollToSection', targetHash);
        sessionStorage.setItem('scrollAnimated', 'true');
        
        // Navigate to profile page
        window.location.href = targetUrl.split('#')[0];
    });
});

// Form submit animation
document.querySelector('form')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-primary');
    submitBtn.classList.add('loading');
    submitBtn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Saving Changes...';
    
    // Disable button to prevent double submission
    submitBtn.disabled = true;
});
</script>
<?php include 'footer.php'; ?>