<?php
include 'database.php';

if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: profile.php#my-listings");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$item_id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "SELECT * FROM items WHERE item_id = '$item_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: profile.php#my-listings");
    exit();
}

$item = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $item_description = mysqli_real_escape_string($conn, $_POST['item_description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $item_price = (!$is_free && isset($_POST['item_price'])) ? floatval($_POST['item_price']) : 0.00;
    
    $file_name = $item['item_image'];

    if (isset($_FILES['item_image']) && !empty($_FILES['item_image']['name'])) {
        $target_dir = "images/uploads/" . $username . "/";
        $file_name = time() . '_' . basename($_FILES["item_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (!move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $error = "Failed to update item snapshot image assets.";
        }
    }

    if (!isset($error)) {
        $update_query = "UPDATE items SET 
                         item_name = '$item_name', 
                         item_description = '$item_description', 
                         category = '$category', 
                         item_price = '$item_price', 
                         is_free = '$is_free', 
                         item_image = '$file_name',
                         status = '$status'
                         WHERE item_id = '$item_id' AND user_id = '$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            header("Location: itemdetail.php?id=" . $item_id);
            exit();
        } else {
            $error = "System error updating records database fields.";
        }
    }
}

$page_title = "Edit Listing | Eco-Connect";
$page_css = "listingform.css";
include 'header.php';
?>

<main class="form-page-wrapper">
    <div class="form-container-card">
        
        <div class="form-header">
            <h1>Edit Listing Details</h1>
            <p>Update tracking fields or description details for your item.</p>
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
                        <input type="file" id="itemImage" name="item_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        
                        <div class="preview-render-stage" id="imagePreview">
                            <img id="previewImg" src="images/uploads/<?php echo $username.'/'.$item['item_image']; ?>" alt="Thumbnail Preview">
                            <div class="preview-overlay-meta">
                                <span id="previewFileName"><?php echo htmlspecialchars($item['item_image']); ?></span>
                                <span class="change-action-badge">Change Image</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Listing Details</h3>
                
                <div class="form-group">
                    <label>Item Name <span class="required">*</span></label>
                    <input type="text" name="item_name" required value="<?php echo htmlspecialchars($item['item_name']); ?>">
                </div>

                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="Electronics" <?php echo ($item['category'] == 'Electronics') ? 'selected' : ''; ?>>📱 Electronics</option>
                        <option value="Furniture" <?php echo ($item['category'] == 'Furniture') ? 'selected' : ''; ?>>🪑 Furniture</option>
                        <option value="Clothing" <?php echo ($item['category'] == 'Clothing') ? 'selected' : ''; ?>>👕 Clothing</option>
                        <option value="Sports" <?php echo ($item['category'] == 'Sports') ? 'selected' : ''; ?>>⚽ Sports</option>
                        <option value="Books" <?php echo ($item['category'] == 'Books') ? 'selected' : ''; ?>>📚 Books</option>
                        <option value="Others" <?php echo ($item['category'] == 'Others') ? 'selected' : ''; ?>>📦 Others</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea 
                    name="item_description" 
                    id="itemDescriptionField" 
                    rows="<?php echo ($item['is_free'] == 1) ? '13' : '17'; ?>" 
                    required><?php echo htmlspecialchars($item['item_description']); ?>
                    </textarea>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Transaction Logistics</h3>

                <div class="form-group">
                    <label>Listing Status <span class="required">*</span></label>
                    <select name="status" required>
                        <option value="available" <?php echo ($item['status'] == 'available') ? 'selected' : ''; ?>>🟢 Active Listing</option>
                        <option value="sold" <?php echo ($item['status'] == 'sold') ? 'selected' : ''; ?>>🔴 Sold Out</option>
                    </select>
                </div>
                
                <div class="form-group inline-checkbox-row">
                    <input type="checkbox" id="isFreeCheckbox" name="is_free" onchange="togglePriceField()" <?php echo ($item['is_free'] == 1) ? 'checked' : ''; ?>>
                    <label for="isFreeCheckbox" class="checkbox-label">This item is a free donation</label>
                </div>
                
                <div id="priceInputWrapper" class="form-group conditional-price-field <?php echo ($item['is_free'] == 1) ? 'hidden-field' : ''; ?>">
                    <label>Asking Price</label>
                    <div class="currency-input-prefix">
                        <span class="prefix-tag">RM</span>
                        <input type="number" step="0.01" min="0" name="item_price" id="itemPriceField" value="<?php echo number_format($item['item_price'], 2, '.', ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-action-footer">
                <a href="profile.php?id=<?php echo $item_id; ?>" class="btn-cancel">Discard</a>
                <button type="submit" class="btn-submit-action">Save Changes</button>
            </div>
        </form>
    </div>
</main>

<script>
function togglePriceField() {
    const isFree = document.getElementById('isFreeCheckbox').checked;
    const wrapper = document.getElementById('priceInputWrapper');
    const field = document.getElementById('itemPriceField');
    const descriptionField = document.getElementById('itemDescriptionField'); // Target the textarea
    
    if (isFree) {
        wrapper.classList.add('hidden-field');
        if(field) field.value = '0';
        
        // Change rows to 13 when it's a giveaway
        if(descriptionField) descriptionField.setAttribute('rows', '13'); 
    } else {
        wrapper.classList.remove('hidden-field');
        
        // Change rows back to 15 when it's a paid item
        if(descriptionField) descriptionField.setAttribute('rows', '17');
    }
}

function previewImage(input) {
    const previewImg = document.getElementById('previewImg');
    const previewFileName = document.getElementById('previewFileName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewFileName.textContent = input.files[0].name;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'footer.php'; ?>