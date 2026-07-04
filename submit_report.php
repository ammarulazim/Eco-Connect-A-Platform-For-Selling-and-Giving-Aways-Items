<?php
include 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report_btn'])) {
    $report_type = mysqli_real_escape_string($conn, $_POST['report_type']);
    $reported_item_id = !empty($_POST['reported_item_id']) ? intval($_POST['reported_item_id']) : "NULL";
    $reporter_id = intval($_SESSION['user_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    $sql = "INSERT INTO reports (report_type, reported_item_id, reporter_id, reason,  status) 
            VALUES ('$report_type', $reported_item_id, $reporter_id, '$reason', 'pending')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Listing flag successfully documented for administration review.'); window.history.back();</script>";
    } else {
        echo "<script>alert('Error logging community flag.'); window.history.back();</script>";
    }
    exit();
}
?>