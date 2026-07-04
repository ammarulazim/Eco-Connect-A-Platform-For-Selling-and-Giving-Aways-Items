<?php
include 'database.php'; // already handles session_start()

header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    echo 0;
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT COUNT(*) AS total 
        FROM notifications 
        WHERE user_id = '$user_id' 
        AND is_read = 0";

$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);

echo (int)($row['total'] ?? 0);
?>