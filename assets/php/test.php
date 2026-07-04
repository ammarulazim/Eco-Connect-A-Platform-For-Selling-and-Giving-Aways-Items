<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">

    <title>Test | Eco-Connect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <div class="form-box login">
            <form action="">
                <h1>Login</h1>
                <div class="input-box">
                    <input type="text" placeholder="Username"
                    required>
                    <i class="bx bx-user"></i>
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Password"
                    required>
                    <i class="bx bx-lock"></i>
                </div>
                <div class="forgot-link">
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" class="btn">Login</button>
                <p>or login with social platforms</p>
                <div class="social-icons">
                    <a href="#"><i class="bxl bx-google"></i></a>
                    <a href="#"><i class="bxl bx-facebook"></i></a>
                    <a href="#"><i class="bxl bx-linkedin"></i></a>
                    <a href="#"><i class="bxl bx-github"></i></a>
                </div>
            </form>
        </div>

        <div class="form-box register">
            <form action="">
                <h1>Registeration</h1>
                <div class="input-box">
                    <input type="text" placeholder="Username"
                    required>
                    <i class="bx bx-user"></i>
                </div>
                <div class="input-box">
                    <input type="email" placeholder="Email"
                    required>
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Password"
                    required>
                    <i class="bx bx-lock"></i>
                </div>
                
                <button type="submit" class="btn">Register</button>
                <p>or register with social platforms</p>
                <div class="social-icons">
                    <a href="#"><i class="bxl bx-google"></i></a>
                    <a href="#"><i class="bxl bx-facebook"></i></a>
                    <a href="#"><i class="bxl bx-linkedin"></i></a>
                    <a href="#"><i class="bxl bx-github"></i></a>
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

    <script src="js/script.js"></script>
</body>
</html>