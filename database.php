<?php
    session_start();

    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "ecoconnectdb";
    
    $conn = mysqli_connect(
        $db_server,
        $db_user,
        $db_pass,
        $db_name
    );

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    /*try{
        $conn = mysqli_connect($db_server, 
                               $db_user, 
                               $db_pass, 
                               $db_name);
    }
    catch(mysqli_sql_exception){
        echo "Database connection error";
    }*/

    /*if ($conn) {
        echo "Database connected successfully.";
    }*/

        
    /*Sign Up Functionality*/
    if (isset($_POST['register_btn'])) {

    $username = $_POST['reg_username'];
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);
    $location = $_POST['reg_location'];

    // check if user exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Username already exists');</script>";
    } else {
        $sql = "INSERT INTO users (username, password, location)
                VALUES ('$username', '$password', '$location')";

        if (mysqli_query($conn, $sql)) {
                $new_user_id = mysqli_insert_id($conn);

                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;

                header("Location: profile.php");
                exit();
            } else {
                echo "<script>alert('Error registering user');</script>";
            }
        }
}
    
    /*Login Funtionality*/
    /*
    if (isset($_POST['login_btn'])) {

                    $username = mysqli_real_escape_string($conn, $_POST['login_username']);
                    $password = mysqli_real_escape_string($conn, $_POST['login_password']);

                    $login_query = "SELECT * FROM users WHERE username='$username'";
                    $result = mysqli_query($conn, $login_query);

                    if (mysqli_num_rows($result) > 0) {

                        $user = mysqli_fetch_assoc($result);

                        // Verify hashed password
                        if (password_verify($password, $user['password'])) {

                            $_SESSION['user_id'] = $user['user_id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];

                            if($user['role'] === 'admin') {
                                header("Location: adminpage.php");
                            } else {
                                header("Location: profile.php");
                            }
                            exit();
                        } else {

                            echo "<script>alert('Incorrect password!');</script>";
                        }
                    } else {

                        echo "<script>alert('User not found!');</script>";
                    }
                }
    */

    // Add item functionality
    // Redirect guest users if they try to access this page directly
    /*if (!isset($_SESSION['user_id'])) {
        header("Location: auth.php");
        exit();
    }*/

    /*Cover Page Upload Functionality*/
    /*Cover Page Upload Functionality*/
    if (isset($_FILES['cover_photo'])) {

        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];

        // FIXED: Explicitly target the images/ directory inside your project root
        $user_folder = "images/uploads/" . $username;

        // Automatically create the folder structure inside images/ if missing
        if (!file_exists($user_folder)) {
            mkdir($user_folder, 0777, true);
        }

        // File details
        $file_name = $_FILES['cover_photo']['name'];
        $tmp_name = $_FILES['cover_photo']['tmp_name'];

        // Get extension safely
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Unique filename string
        $new_file_name = "cover_" . time() . "." . $file_ext;

        // Final physical file path: "images/uploads/username/cover_12345.jpg"
        $target_file = $user_folder . "/" . $new_file_name;

        // Move uploaded file out of server memory directly into htdocs filesystem
        if (move_uploaded_file($tmp_name, $target_file)) {

            // Save ONLY the clean filename string in the database column
            $update_query = "UPDATE users 
                            SET cover_pic='$new_file_name' 
                            WHERE user_id='$user_id'";

            mysqli_query($conn, $update_query);

            // Clean page redirect to refresh visual states
            header("Location: profile.php");
            exit();
        }
    }

    /* Profile Image Upload Functionality */
    if (isset($_FILES['profile_image'])) {

        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];

        // Automatically matches your exact cover photo folder strategy
        $user_folder = "images/uploads/" . $username;

        if (!file_exists($user_folder)) {
            mkdir($user_folder, 0777, true);
        }

        // File details handling parameters
        $file_name = $_FILES['profile_image']['name'];
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Create a unique name to prevent duplicate browser cache conflicts
        $new_avatar_name = "avatar_" . time() . "." . $file_ext;
        $target_file = $user_folder . "/" . $new_avatar_name;

        // Move the file into htdocs folder and update table metadata
        if (move_uploaded_file($tmp_name, $target_file)) {

            $update_avatar_query = "UPDATE users 
                                    SET profile_image='$new_avatar_name' 
                                    WHERE user_id='$user_id'";

            if (mysqli_query($conn, $update_avatar_query)) {
                // Refresh to render the fresh asset change instantly
                header("Location: profile.php");
                exit();
            }
        }
    }
?>