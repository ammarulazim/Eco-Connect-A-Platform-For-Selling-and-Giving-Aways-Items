<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $item_id = intval($_POST['item_id']);
    $session_user_id = intval($_SESSION['user_id']); 
    $location = mysqli_real_escape_string($conn, $_POST['meeting_location'] ?? 'Not Specified');

    // 1. Fetch item details directly from the DB to get the true seller
    $item_res = mysqli_query($conn, "SELECT item_name, item_price, user_id FROM items WHERE item_id = $item_id LIMIT 1");
    if ($item_data = mysqli_fetch_assoc($item_res)) {
        $item_name = mysqli_real_escape_string($conn, $item_data['item_name']);
        $price = floatval($item_data['item_price']); 
        $seller_id = intval($item_data['user_id']); // The item owner is ALWAYS the seller
        
        // 2. Determine who the buyer is dynamically
        if ($session_user_id === $seller_id) {
            // Context A: The Seller initiated the deal from the chat modal
            $buyer_id = intval($_POST['buyer_id']);
            $redirect_id = $buyer_id;
        } else {
            // Context B: The Buyer clicked "Chat Now" from an item listing card
            $buyer_id = $session_user_id;
            $redirect_id = $seller_id;
        }

        $deal_type = ($price == 0) ? 'donation' : 'purchase';

        // 3. Create the official structural transaction log row
        $order_sql = "INSERT INTO orders (item_id, buyer_id, seller_id, deal_type, price, meeting_location, payment_method, status) 
                      VALUES ($item_id, $buyer_id, $seller_id, '$deal_type', $price, '$location', '', 'pending')";
        
        if (mysqli_query($conn, $order_sql)) {
            $generated_order_id = mysqli_insert_id($conn);

            // 4. Inject structural card data token directly into chat rows stream
            $msg_text = "Sent an Official Deal Proposal for: " . $item_name;
            $msg_sql = "INSERT INTO messages (sender_id, receiver_id, message_text, context_item, order_id) 
                        VALUES ('$session_user_id', '$redirect_id', '$msg_text', '$item_name', $generated_order_id)";
            mysqli_query($conn, $msg_sql);
        }
        
        header("Location: messaging.php?to_id=" . $redirect_id);
        exit();
    }
}

header("Location: messaging.php");
exit();