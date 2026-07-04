<?php
    include 'database.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $page_title = "Home | Eco-Connect";
    $page_css = "index.css";
    $page_script = "script3.js";

// Check if the user has an active warning flag stored in their session
if (isset($_SESSION['show_warning_popup']) && $_SESSION['show_warning_popup'] === true): 
?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Administrative Warning',
                text: '<?php echo addslashes($_SESSION['warning_text']); ?>',
                icon: 'warning',
                confirmButtonColor: '#9ec55e', /* Matches your Eco-Connect dominant green */
                confirmButtonText: 'I Understand',
                background: document.body.classList.contains('dark-mode') ? '#1e1e1e' : '#ffffff',
                color: document.body.classList.contains('dark-mode') ? '#ffffff' : '#000000',
                customClass: {
                    popup: 'premium-popup-border'
                }
            });
        });
    </script>
<?php 
    // Clear the flag instantly so the pop-up doesn't endlessly annoy them on every page refresh
    unset($_SESSION['show_warning_popup']);
    unset($_SESSION['warning_text']);
endif;

include 'header.php';
?>

    <main class="index-wrapper"> 
        <div class="carousel-container">
            <div class="carousel-slide active">
                <img src="images/homeslide/1.png" alt="Aesthetic Outdoor Street Market Stall">
                <div class="carousel-caption">
                    <h1>Welcome to Eco-Connect</h1>
                    <p>Connecting local residents to a sustainable, waste-free future.</p>
                    <a href="aboutus.php" class="carousel-btn">Learn Our Story</a>
                </div>
            </div>

            <div class="carousel-slide">
                <img src="images/homeslide/2.png" alt="Aesthetic Thrift Market Display">
                <div class="carousel-caption">
                    <h1>Give Away Unused Items</h1>
                    <p>Bridge the clutter gap by passing functional goods to neighbors who need them.</p>
                    <a href="auth.php" class="carousel-btn">Start Sharing</a>
                </div>
            </div>

            <div class="carousel-slide">
                <img src="images/homeslide/3.png" alt="Aesthetic Retro Second Hand Store">
                <div class="carousel-caption">
                    <h1>Hyper-Local Marketplace</h1>
                    <p>Buy and sell second-hand goods securely within your local neighborhood.</p>
                    <a href="auth.php" class="carousel-btn">Browse Items</a>
                </div>
            </div>

            <button class="carousel-arrow prev" onclick="moveSlide(-1)">&#10094;</button>
            <button class="carousel-arrow next" onclick="moveSlide(1)">&#10095;</button>

            <div class="carousel-dots">
                <span class="dot active" onclick="currentSlide(0)"></span>
                <span class="dot" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
            </div>
        </div>

        <div class="container" style="padding: 60px 40px; color: white;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap;">
            <h2 style="font-size: 2rem; margin: 0;">Explore Nearby Exchanges</h2>
            
            <div class="search-box" style="position: relative; flex: 1; max-width: 400px; min-width: 250px;">
                <i class='bx bx-search' style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #a4b8ae; font-size: 1.2rem;"></i>
                <input type="text" id="itemSearch" placeholder="Search items..." style="width: 100%; background: #162a20; border: 1px solid rgba(255,255,255,0.1); border-radius: 30px; padding: 12px 20px 12px 45px; color: white; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s;">
            </div>

            <?php // if (isset($_SESSION['user_id'])): ?>
                <!-- <a href="additem.php" class="add-item-shortcut-btn" style="margin: 0;">+ List New Item</a> -->
            <?php //endif; ?>
        </div>

        <div class="items-section">
            <div class="items-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap:30px;">
                <?php
                // FIXED QUERY: Joins user location details records alongside specific listing metrics data sets
                $query = "
                    SELECT items.*, users.username
                    FROM items
                    JOIN users ON items.user_id = users.user_id
                    ORDER BY items.created_at DESC
                ";
                $result = mysqli_query($conn, $query);
                while ($item = mysqli_fetch_assoc($result)) {
                    $is_sold = ($item['status'] === 'sold');
                ?>
                    <div class="item-card" style="background:#162a20; border-radius:12px; overflow:hidden; display:flex; flex-direction:column; position:relative; border:1px solid rgba(255,255,255,0.05);">
                        <!--
                        <div style="position:relative; width:100%; height:200px; background:#0f1e17;">
                            <img src="images/uploads/<?php echo $item['username']; ?>/<?php echo $item['item_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                 style="width:100%; height:100%; object-fit:cover; display:block; filter: <?php echo $is_sold ? 'grayscale(100%) brightness(40%)' : 'none'; ?>;">
                            
                            <?php if($is_sold): ?>
                                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:#cc4e4e; color:white; padding:8px 20px; font-weight:800; border-radius:4px; font-size:1.1rem; text-transform:uppercase; letter-spacing:1px; box-shadow:0 4px 10px rgba(0,0,0,0.3);">Sold</div>
                            <?php else: ?>
                                <span style="position:absolute; top:15px; right:15px; background:<?php echo $item['is_free'] ? '#9ec55e' : '#1c3529'; ?>; color:<?php echo $item['is_free'] ? '#123426' : '#ffffff'; ?>; padding:5px 12px; border-radius:20px; font-size:0.82rem; font-weight:700;">
                                    <?php echo $item['is_free'] ? 'FREE' : 'RM ' . number_format($item['item_price'], 2); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        -->
                        <a href="itemdetail.php?id=<?php echo $item['item_id']; ?>" style="display:block; position:relative; width:100%; height:200px; background:#0f1e17; overflow:hidden;">
                            <img src="images/uploads/<?php echo $item['username']; ?>/<?php echo $item['item_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                 style="width:100%; height:100%; object-fit:cover; display:block; transition: transform 0.3s ease; filter: <?php echo $is_sold ? 'grayscale(100%) brightness(40%)' : 'none'; ?>;"
                                 onmouseover="this.style.transform='scale(1.04)'"
                                 onmouseout="this.style.transform='scale(1)'">
                            
                            <?php if($is_sold): ?>
                                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:#cc4e4e; color:white; padding:8px 20px; font-weight:800; border-radius:4px; font-size:1.1rem; text-transform:uppercase; letter-spacing:1px; box-shadow:0 4px 10px rgba(0,0,0,0.3); z-index:2;">Sold</div>
                            <?php else: ?>
                                <span style="position:absolute; top:15px; right:15px; background:<?php echo $item['is_free'] ? '#9ec55e' : '#1c3529'; ?>; color:<?php echo $item['is_free'] ? '#123426' : '#ffffff'; ?>; padding:5px 12px; border-radius:20px; font-size:0.82rem; font-weight:700; z-index:2;">
                                    <?php echo $item['is_free'] ? 'FREE' : 'RM ' . number_format($item['item_price'], 2); ?>
                                </span>
                            <?php endif; ?>
                        </a>

                        <div style="padding:20px; display:flex; flex-direction:column; flex:1;">
                            <h3 style="margin:0 0 10px 0; font-size:1.2rem; color:white;"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                            <p style="margin:0 0 20px 0; font-size:0.9rem; color:#9cb1a6; line-height:1.5; flex:1;"><?php echo htmlspecialchars($item['item_description']); ?></p>
                            
                            <div style="border-top:1px solid rgba(255,255,255,0.08); padding-top:12px; font-size:0.8rem; color:#8fa399; display:flex; flex-direction:column; gap:4px;">
                                <span><i class="fa-solid fa-location-dot" style="color:#9ec55e;"></i> <?php echo htmlspecialchars($item['location']); ?></span>
                                <span>Posted by <strong style="color:white;"><?php echo ucfirst($item['username']); ?></strong></span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div id="noResultsMessage" style="display: none; text-align: center; padding: 60px 20px; color: #9cb1a6;">
                <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #1c3529; margin-bottom: 15px; display: block;"></i>
                <h3 style="color: white; margin-bottom: 5px;">No items found</h3>
                <p style="margin: 0; font-size: 0.95rem;">We couldn't find anything matching your search. Try checking your spelling or using different keywords.</p>
            </div>
        </div>
    </main>

    <?php if (isset($_SESSION['show_warning_popup']) && $_SESSION['show_warning_popup'] === true): ?>
        <div id="warningModalOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 99999; display: flex; align-items: center; justify-content: center;">
            
            <div style="background: #ffffff; width: 90%; max-width: 450px; padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center; border-top: 5px solid #ffcc00; animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
                
                <div style="font-size: 3rem; margin-bottom: 15px;">⚠️</div>
                <h2 style="margin: 0 0 10px 0; font-family: sans-serif; color: #333;">Account Notice</h2>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 25px;">
                    <?php echo htmlspecialchars($_SESSION['warning_text']); ?>
                </p>
                
                <button onclick="dismissWarningModal()" style="background: #9ec55e; color: #fff; border: none; padding: 12px 30px; font-size: 1rem; font-weight: 600; border-radius: 8px; cursor: pointer; transition: background 0.2s; width: 100%;">
                    I Understand
                </button>
            </div>
        </div>

        <style>
            @keyframes modalPop {
                from { transform: scale(0.8); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
        </style>

        <script>
            function dismissWarningModal() {
                document.getElementById('warningModalOverlay').remove();
            }
        </script>
    <?php 
        // Unset instantly so it doesn't harass them on the next page click
        unset($_SESSION['show_warning_popup']);
        unset($_SESSION['warning_text']);
    endif; 
    ?>

<?php include 'footer.php'; ?>