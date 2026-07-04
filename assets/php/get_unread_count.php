<?php
include 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo 0;
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT COUNT(*) AS total 
        FROM messages 
        WHERE receiver_id = '$user_id' AND is_read = 0";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

echo $row['total'];
?>