<?php
include 'database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// 1. Handle User Status Changes (Warn, Ban, Activate)
if (isset($_POST['action_type']) && $_POST['action_type'] === 'moderate_user') {
    $target_uid = intval($_POST['user_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $warning_msg = mysqli_real_escape_string($conn, $_POST['warning_message'] ?? '');

    mysqli_query($conn, "UPDATE users SET status = '$new_status', warning_message = '$warning_msg' WHERE user_id = $target_uid");
    header("Location: adminpage.php?msg=User status updated successfully");
    exit();
}

// 2. Handle Resolving / Deleting Reports
if (isset($_GET['resolve_report'])) {
    $report_id = intval($_GET['resolve_report']);
    mysqli_query($conn, "UPDATE reports SET status = 'resolved' WHERE report_id = $report_id");
    header("Location: adminpage.php?msg=Report marked resolved");
    exit();
}

// 🟩 ACTION 1: RESOLVE REPORT (Clear Flag)
if (isset($_GET['resolve_report'])) {
    $report_id = intval($_GET['resolve_report']);
    
    $update_sql = "UPDATE reports SET status = 'resolved' WHERE report_id = $report_id";
    if (mysqli_query($conn, $update_sql)) {
        header("Location: adminpage.php?msg=Report+marked+resolved");
    } else {
        header("Location: adminpage.php?error=Failed+to+resolve+report");
    }
    exit();
}

// 🟨 ACTION 2: MODERATE USER MODAL FORM HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'moderate_user') {
    $user_id = intval($_POST['user_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $warning_message = mysqli_real_escape_string($conn, $_POST['warning_message']);

    $update_user = "UPDATE users SET status = '$status', warning_message = '$warning_message' WHERE user_id = $user_id";
    if (mysqli_query($conn, $update_user)) {
        header("Location: adminpage.php?msg=User+enforcement+applied");
    } else {
        header("Location: adminpage.php?error=Failed+to+update+user");
    }
    exit();
}
?>