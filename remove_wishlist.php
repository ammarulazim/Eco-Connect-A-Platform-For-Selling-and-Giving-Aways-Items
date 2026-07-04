<?php
include 'database.php';

// Quick authentication check protection
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access rules declaration violation.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$item_id = mysqli_real_escape_string($conn, $_GET['id']);

// Execute the drop target statement query
$delete_sql = "DELETE FROM wishlist WHERE user_id = '$user_id' AND item_id = '$item_id'";

if (mysqli_query($conn, $delete_sql)) {
    // Return structured JSON data back up to the JavaScript fetch mechanism
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database execution exception encountered.']);
}
exit();
?>