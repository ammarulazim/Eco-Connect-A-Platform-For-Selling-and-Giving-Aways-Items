<?php
    include 'database.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $error_msg = ""; 

    // 🔗 FIX 1: Catch the redirect error string parameters from the URL
    if (isset($_GET['error'])) {
        $error_msg = htmlspecialchars($_GET['error']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
        $username = mysqli_real_escape_string($conn, $_POST['login_username']);
        $password = $_POST['login_password']; 

        // Fetch the user, making sure your database column name matches 'status'
        $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
        
        if ($query && mysqli_num_rows($query) > 0) {
            $user_data = mysqli_fetch_assoc($query);

            // 🔐 DUAL PASSWORD CHECK: Verifies secure hashes AND fallback plaintext for old test rows
            $is_password_valid = password_verify($password, $user_data['password']) || ($password === $user_data['password']);

            if ($is_password_valid) { 
                
                // Clear whitespace and force lowercase to prevent string matching bugs
                $current_status = isset($user_data['status']) ? trim(strtolower($user_data['status'])) : '';
                
                // ❌ CASE 1: USER IS BANNED
                if ($current_status === 'banned') {
                    // Fetch the unique warning/ban message from your database column
                    $reason = !empty($user_data['warning_message']) ? $user_data['warning_message'] : "Your account has been permanently suspended by administration.";
                    $error_msg = "🚫 " . $reason;
                    
                    // Redirect back to login while passing the message safely
                    header("Location: auth.php?error=" . urlencode($error_msg));
                    exit();
                } 

                // ⚠️ CASE 2: USER IS WARNED (Allow login, pass message to session)
                else if ($current_status === 'warned') {
                    $_SESSION['user_id'] = $user_data['user_id']; 
                    $_SESSION['username'] = $user_data['username'];
                    $_SESSION['role'] = $user_data['role'];

                    $_SESSION['show_warning_popup'] = true;
                    $_SESSION['warning_text'] = !empty($user_data['warning_message']) ? $user_data['warning_message'] : "Please review our platform guidelines.";

                    header("Location: index.php");
                    exit();
                }

                // ✅ CASE 3: USER IS ACTIVE
                else {
                    $_SESSION['user_id'] = $user_data['user_id'];
                    $_SESSION['username'] = $user_data['username'];
                    $_SESSION['role'] = $user_data['role'];
                    
                    if($user_data['role'] === 'admin') {
                        header("Location: adminpage.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                }

            } else {
                $error_msg = "Invalid username or password combinations.";
            }
        } else {
            $error_msg = "Invalid username or password combinations.";
        }
    }

    $page_title = "Authentication | Eco-Connect";
    $page_css = "auth.css";
    $page_script = "script.js";
    include 'header.php';
?>

    <div class="auth-wrapper">
        <div class="container">
            <div class="form-box login">
                <form method="POST">
                    <h1>Login</h1>
                    
                    <?php if (!empty($error_msg)): ?>
                        <div class="login-error-alert" style="background-color: #ffe0e3; color: #ff4757; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-weight: 500; border-left: 4px solid #ff4757; font-size: 0.9rem; text-align: left;">
                            <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-box">
                        <input type="text" name="login_username" placeholder="Username" required>
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="input-box">
                        <input type="password" name="login_password" placeholder="Password" required>
                        <i class="bx bx-lock"></i>
                    </div>
                    <div class="forgot-link">
                        <a href="#">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn" name="login_btn">Login</button>
                    <p>or login with social platforms</p>
                    <div class="social-icons">
                        <a href="#"><i class="bx bxl-google"></i></a>
                        <a href="#"><i class="bx bxl-facebook"></i></a>
                        <a href="#"><i class="bx bxl-linkedin"></i></a>
                        <a href="#"><i class="bx bxl-github"></i></a>
                    </div>
                </form>
            </div>

            <div class="form-box register">
                <form method="POST">
                    <h1>Sign Up</h1>
                    <div class="input-box">
                        <input type="text" name="reg_username" placeholder="Username" required>
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="input-box">
                        <input type="password" name="reg_password" placeholder="Password" required>
                        <i class="bx bx-lock"></i>
                    </div>
                    <div class="input-box" style="position: relative;">
                        <input type="text" id="regLocation" name="reg_location" placeholder="Location" required autocomplete="off">
                        <i class="bx bx-map"></i>
                        <div id="locationSuggestions" class="location-suggestions-box"></div>
                    </div>

                    <button type="submit" class="btn" name="register_btn">Register</button>
                    <p>or register with social platforms</p>
                    <div class="social-icons">
                        <a href="#"><i class="bx bxl-google"></i></a>
                        <a href="#"><i class="bx bxl-facebook"></i></a>
                        <a href="#"><i class="bx bxl-linkedin"></i></a>
                        <a href="#"><i class="bx bxl-github"></i></a>
                    </div>
                </form>
            </div>

            <div class="toggle-box">
                <div class="toggle-panel toggle-left">
                    <h1>Hello, Welcome!</h1>
                    <p>Don't have an account?</p>
                    <button class="btn register-btn">Register</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Welcome Back!</h1>
                    <p>Already have an account?</p>
                    <button class="btn login-btn">Login</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>