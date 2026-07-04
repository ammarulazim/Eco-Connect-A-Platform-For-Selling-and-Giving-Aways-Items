<?php
include 'database.php';

// 1. Base Security: If not logged in at all, bounce immediately
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Direct Database Verification Check
$verify_user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
$admin_verify_query = mysqli_query($conn, "SELECT role FROM users WHERE user_id = '$verify_user_id'");
$user_data = mysqli_fetch_assoc($admin_verify_query);

// Check if their true database status matches the 'admin' keyword string value
if (!$user_data || strtolower($user_data['role']) !== 'admin') {
    header("Location: index.php");
    exit();
}

// 3. Process Manual Item Deletion Route securely
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $item_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Clean up relational references to bypass Foreign Key Constraint blocks safely
    mysqli_query($conn, "DELETE FROM wishlist WHERE item_id = '$item_id'");
    mysqli_query($conn, "DELETE FROM reports WHERE reported_item_id = '$item_id'");
    
    // Core structural item deletion line execution
    $delete_query = "DELETE FROM items WHERE item_id = '$item_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        // Success -> Land cleanly back inside your management desk
        header("Location: adminpage.php?msg=takedown_success");
        exit();
    } else {
        die("MySQL Error Exception Block: " . mysqli_error($conn));
    }
} else {
    header("Location: adminpage.php");
    exit();
}
?>