<?php
include 'database.php';

if (isset($_SESSION['user_id']) && isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $item_id = intval($_GET['id']);
    
    // Safety check pathing validation loop
    $delete_query = "DELETE FROM items WHERE item_id = '$item_id' AND user_id = '$user_id'";
    mysqli_query($conn, $delete_query);
}

header("Location: profile.php");
exit();