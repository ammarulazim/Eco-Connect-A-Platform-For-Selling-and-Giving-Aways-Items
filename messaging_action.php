<?php
include 'database.php';

if (isset($_GET['order_id']) && isset($_GET['status']) && isset($_SESSION['user_id'])) {
    $order_id = intval($_GET['order_id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']); 
    $redirect_user = intval($_GET['to_id']);
    
    $method = isset($_GET['method']) ? mysqli_real_escape_string($conn, $_GET['method']) : 'Handover';
    $bank = (isset($_GET['bank_name']) && $method === 'Online Banking') ? mysqli_real_escape_string($conn, $_GET['bank_name']) : NULL;

    if (in_array($status, ['accepted', 'declined'])) {
        // Update transaction row values with payment specifications
        $bank_val = $bank ? "'$bank'" : "NULL";
        mysqli_query($conn, "UPDATE orders SET status = '$status', payment_method = '$method', bank_name = $bank_val WHERE order_id = $order_id");

        // Sync and turn off availability across item maps instantly if checked through successfully
        if ($status === 'accepted') {
            $lookup = mysqli_query($conn, "SELECT item_id FROM orders WHERE order_id = $order_id LIMIT 1");
            if ($row = mysqli_fetch_assoc($lookup)) {
                $item_id = intval($row['item_id']);
                mysqli_query($conn, "UPDATE items SET status = 'sold' WHERE item_id = $item_id");
            }
        }
    }

    header("Location: messaging.php?to_id=" . $redirect_user);
    exit();
}