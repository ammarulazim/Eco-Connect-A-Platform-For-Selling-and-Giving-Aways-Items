<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <link rel="shortcut icon" type="image/png" href="images/logo/logo.png" />
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">

    <?php
        if (isset($page_css)) {
            echo '<link rel="stylesheet" href="css/' . $page_css . '">';
        }
    ?>

    <title>
        <?php echo isset($page_title) ? $page_title : "Eco-Connect"; ?>
    </title>
</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="header-sidebar-overlay" id="sidebarOverlay" onclick="toggleHeaderSidebar()"></div>
        
        <aside class="header-sidebar" id="headerSidebar">
            <div class="sidebar-header-box">
                <div class="sidebar-logo-brand">
                    <img src="images/logo/logo.png" alt="Eco-Connect Logo">
                    <span>Eco-Connect</span>
                </div>
                <button class="sidebar-close-trigger-btn" onclick="toggleHeaderSidebar()">
                    <i class='bx bx-x'></i>
                </button>
            </div>
            
            <nav class="sidebar-link-navigation-menu">
                <ul>
                    <li>
                        <a href="index.php">
                            <i class='bx bx-home-alt-2'></i> <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="aboutus.php">
                            <i class='bx bx-info-circle'></i> <span>About Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="notifications.php">
                            <i class='bx bx-bell'></i> <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="wishlist.php">
                            <i class='bx bx-heart'></i> <span>My Wishlist</span>
                        </a>
                    </li>
                    <li>
                        <a href="messaging.php">
                            <i class='bx bx-message-square-detail'></i> <span>Messages</span>
                        </a>
                    </li>
                    <li class="sidebar-divider-rule"></li>
                    <li>
                        <a href="logout.php" class="sidebar-logout-link">
                            <i class='bx bx-log-out'></i> <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
    <?php endif; ?>

    <header id="navbar">
    
        <div class="nav-left-wrapper">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="header-hamburger-toggle" id="headerNavToggleBtn" onclick="toggleHeaderSidebar()" title="Open Navigation Menu">
                    <i class='bx bx-menu'></i>
                </button>
            <?php endif; ?>

            <a class="topleft" href="index.php">
                 <img id="logo" src="images/logo/logo.png" alt="Eco-Connect Logo"> Eco-Connect
            </a>
        </div>

        <div class="topright">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="notifications.php" class="icon-btn" title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <span class="icon-badge" id="notifBadge" style="display:none;">0</span>
                </a>
                <a href="messaging.php" class="icon-btn" title="Messages">
                    <i class="fa-solid fa-message"></i>
                    <span class="icon-badge" id="msgBadge" style="display:none;">0</span>
                </a>
                <a href="wishlist.php" class="icon-btn" title="Wishlist">
                    <i class="fa-solid fa-heart"></i>
                </a>
            <?php else: ?>
                <a href="index.php">Home</a>
                <a href="aboutus.php">About Us</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['username'])): ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="adminpage.php" class="profile-nav-link-item">
                        <i class="fa-solid fa-user-shield"></i> <?php echo ucfirst($_SESSION['username']); ?>
                    </a>
                <?php else: ?>
                    <a href="profile.php" class="profile-nav-link-item">
                        <i class="fa-regular fa-user"></i> <?php echo ucfirst($_SESSION['username']); ?>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="auth.php" class="login-nav-btn-highlight">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <script>
    function toggleHeaderSidebar() {
        const sidebar = document.getElementById('headerSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if(sidebar && overlay) {
            sidebar.classList.toggle('open-sidebar-canvas');
            overlay.classList.toggle('active-blur-shading');
        }
    }

    function loadNotifications() {
        fetch('get_notifications_count.php')
            .then(res => res.text())
            .then(count => {
                const badge = document.getElementById('notifBadge');
                if (!badge) return;

                count = parseInt(count);
                if (isNaN(count)) count = 0;

                if (count > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = count > 99 ? '99+' : count;
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.log(err));
    }

    <?php if (isset($_SESSION['user_id'])): ?>
    loadNotifications();
    setInterval(loadNotifications, 4000);
    <?php endif; ?>
    </script>
</body>
</html>