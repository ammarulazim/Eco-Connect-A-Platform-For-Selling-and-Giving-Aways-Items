<?php
include 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $item_description = mysqli_real_escape_string($conn, $_POST['item_description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $item_price = (!$is_free && isset($_POST['item_price'])) ? floatval($_POST['item_price']) : 0.00;
    
    $loc_query = "SELECT location FROM users WHERE user_id = '$user_id'";
    $loc_result = mysqli_query($conn, $loc_query);
    $user_row = mysqli_fetch_assoc($loc_result);
    $automatically_captured_location = mysqli_real_escape_string($conn, $user_row['location'] ?? 'Puchong');

    $target_dir = "images/uploads/" . $username . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES["item_image"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
        $insert_query = "INSERT INTO items (user_id, item_name, item_description, category, item_price, is_free, item_image, location, status) 
                         VALUES ('$user_id', '$item_name', '$item_description', '$category', '$item_price', '$is_free', '$file_name', '$automatically_captured_location', 'available')";
        
        if (mysqli_query($conn, $insert_query)) {
            header("Location: profile.php#my-listings?new=success");
            exit();
        } else {
            $error = "Database error occurred. Please try again.";
        }
    } else {
        $error = "Error uploading image. Please check file size and format.";
    }
}

$page_title = "List New Item | Eco-Connect";
$page_css = "listingform.css";
include 'header.php';
?>

<main class="profile-wrapper">
    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="separator">/</span>
        <a href="profile.php#my-listings" class="breadcrumb-link">My Items</a>
        <span class="separator">/</span>
        <span class="current">Add New Item</span>
    </div>

    <div class="profile-main-card">
        <div class="card-header">
            <h2>
                <i class="fa-solid fa-plus-circle"></i>
                List New Item
            </h2>
            <p>Share functional goods with your local community</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error" style="margin: 0 var(--spacing-2xl) var(--spacing-xl) var(--spacing-2xl);">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">Select category</option>
                        <option value="Electronics">📱 Electronics</option>
                        <option value="Furniture">🪑 Furniture</option>
                        <option value="Clothing">👕 Clothing</option>
                        <option value="Sports">⚽ Sports</option>
                        <option value="Books">📚 Books</option>
                        <option value="Others">📦 Others</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Item Name <span class="required">*</span></label>
                    <input type="text" name="item_name" required placeholder="e.g., Samsung Galaxy S21">
                </div>

                <div class="form-group form-group-full">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="item_description" rows="4" required placeholder="Describe your item condition, features, and any important details..."></textarea>
                    <div class="form-hint">Be specific about the condition, age, and any defects</div>
                </div>

                <div class="form-group form-group-full">
                    <div class="price-section">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="isFreeCheckbox" name="is_free" onchange="togglePriceField()">
                            <label for="isFreeCheckbox">🎁 This item is free (Donation)</label>
                        </div>
                        
                        <div id="priceInputWrapper" style="display: none; margin-top: 1rem;">
                            <label>Asking Price (RM)</label>
                            <input type="number" step="0.01" min="0" name="item_price" id="itemPriceField" placeholder="0.00">
                            <div class="form-hint">Suggest a fair market price</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Item Image <span class="required">*</span></label>
                    <div class="file-upload" onclick="document.getElementById('itemImage').click()">
                        <div class="file-upload-label">
                            <i class="fa-solid fa-cloud-upload-alt"></i>
                            <span>Upload image</span>
                            <span class="small">PNG, JPG up to 5MB</span>
                        </div>
                        <input type="file" id="itemImage" name="item_image" accept="image/*" required style="display: none;" onchange="previewImage(this)">
                    </div>
                    <div id="imagePreview" style="display: none; margin-top: var(--spacing-md); padding: var(--spacing-md); background: rgba(158, 197, 94, 0.1); border-radius: var(--radius-lg); border: 1px solid var(--secondary-500);">
                        <div class="image-preview-container">
                            <div class="image-preview">
                                <img id="previewImg" src="" alt="Image preview">
                            </div>
                            <div class="image-info">
                                <p><i class="fa-regular fa-check-circle"></i> Image selected</p>
                                <p id="previewFileName"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-cloud-upload-alt"></i> Publish Listing
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
        if(field) field.value = '0';
    } else {
        wrapper.style.display = 'block';
    }
}

function previewImage(input) {
    const previewDiv = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const previewFileName = document.getElementById('previewFileName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
            previewFileName.innerHTML = '<i class="fa-regular fa-file"></i> ' + input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
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
        
        sessionStorage.setItem('scrollToSection', targetHash);
        sessionStorage.setItem('scrollAnimated', 'true');
        
        window.location.href = targetUrl.split('#')[0];
    });
});

// Form submit animation
document.querySelector('form')?.addEventListener('submit', function() {
    const submitBtn = this.querySelector('.btn-primary');
    submitBtn.classList.add('loading');
    submitBtn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Publishing...';
    submitBtn.disabled = true;
});
</script>

<?php include 'footer.php'; ?>