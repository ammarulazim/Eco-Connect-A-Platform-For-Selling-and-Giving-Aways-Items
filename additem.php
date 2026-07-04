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
            header("Location: profile.php#my-listings");
            exit();
        } else {
            $error = "Database error occurred. Please try again.";
        }
    } else {
        $error = "Error uploading image. Please check file size and format.";
    }
}

$page_title = "Create a Listing | Eco-Connect";
$page_css = "listingform.css";
include 'header.php';
?>

<main class="form-page-wrapper">
    <div class="form-container-card">
        
        <div class="form-header">
            <h1>Create New Listing</h1>
            <p>List item transparently to the local community.</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="listingForm">
            
            <div class="form-section">
                <h3 class="section-title">Item Media</h3>
                <div class="form-group">
                    <label>Product Image <span class="required">*</span></label>
                    <div class="media-upload-dropzone" onclick="document.getElementById('itemImage').click()">
                        <div class="upload-prompt-content" id="uploadPrompt">
                            <i class="fa-solid fa-camera"></i>
                            <span>Upload a crisp image of your item</span>
                            <span class="mime-hint">PNG, JPG up to 5MB</span>
                        </div>
                        <input type="file" id="itemImage" name="item_image" accept="image/*" required style="display: none;" onchange="previewImage(this)">
                        
                        <div class="preview-render-stage" id="imagePreview" style="display: none;">
                            <img id="previewImg" src="" alt="Thumbnail Preview">
                            <div class="preview-overlay-meta">
                                <span id="previewFileName">filename.jpg</span>
                                <span class="change-action-badge">Replace</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Listing Details</h3>
                
                <div class="form-group">
                    <label>Item Name <span class="required">*</span></label>
                    <input type="text" name="item_name" required placeholder="What are you listing?">
                </div>

                <!--
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Electronics">📱 Electronics</option>
                        <option value="Furniture">🪑 Furniture</option>
                        <option value="Clothing">👕 Clothing</option>
                        <option value="Sports">⚽ Sports</option>
                        <option value="Books">📚 Books</option>
                        <option value="Others">📦 Others</option>
                    </select>
                </div>
                -->
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <option value="Electronics">📱 Electronics</option>
                            <option value="Furniture">🪑 Furniture</option>
                            <option value="Clothing">👕 Clothing</option>
                            <option value="Sports">⚽ Sports</option>
                            <option value="Books">📚 Books</option>
                            <option value="Others">📦 Others</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="item_description" rows="13" required placeholder="Describe condition, pickup arrangements, etc..."></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Transaction Logistics</h3>
                
                <div class="form-group inline-checkbox-row">
                    <input type="checkbox" id="isFreeCheckbox" name="is_free" onchange="togglePriceField()">
                    <label for="isFreeCheckbox" class="checkbox-label">This item is a free donation</label>
                </div>
                
                <div id="priceInputWrapper" class="form-group conditional-price-field">
                    <label>Asking Price</label>
                    <div class="currency-input-prefix">
                        <span class="prefix-tag">RM</span>
                        <input type="number" step="0.01" min="0" name="item_price" id="itemPriceField" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="form-action-footer">
                <a href="profile.php#my-listings" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit-action">Publish Listing</button>
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
        wrapper.classList.add('hidden-field');
        if(field) field.value = '0';
    } else {
        wrapper.classList.remove('hidden-field');
    }
}

function previewImage(input) {
    const promptDiv = document.getElementById('uploadPrompt');
    const previewDiv = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const previewFileName = document.getElementById('previewFileName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            promptDiv.style.display = 'none';
            previewDiv.style.display = 'block';
            previewFileName.textContent = input.files[0].name;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'footer.php'; ?>