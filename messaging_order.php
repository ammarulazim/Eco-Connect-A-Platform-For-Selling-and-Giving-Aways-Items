<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $session_user_id = intval($_SESSION['user_id']);
    
    // Context A: Buyer clicked "Chat Now" from an external listing page
    if (isset($_POST['buyer_intent']) && isset($_POST['item_id'])) {
        $item_id = intval($_POST['item_id']);
        
        // Find the owner of this item
        $item_res = mysqli_query($conn, "SELECT item_name, user_id FROM items WHERE item_id = $item_id LIMIT 1");
        if ($item_data = mysqli_fetch_assoc($item_res)) {
            $item_name = mysqli_real_escape_string($conn, $item_data['item_name']);
            $seller_id = intval($item_data['user_id']);
            
            // Send an Inquiry Message marked with the specific item context
            $msg_text = "I am interested in buying this item. Please review my request!";
            $msg_sql = "INSERT INTO messages (sender_id, receiver_id, message_text, context_item) 
                        VALUES ('$session_user_id', '$seller_id', '$msg_text', '$item_id')"; // Storing item_id in context_item temporarily
            mysqli_query($conn, $msg_sql);
            
            header("Location: messaging.php?to_id=" . $seller_id);
            exit();
        }
    }
    
    // Context B: Owner filled out the pre-filled proposal modal from inside the chat
    if (isset($_POST['owner_proposal'])) {
        $item_id = intval($_POST['item_id']);
        $buyer_id = intval($_POST['buyer_id']); // Taken from the form hidden input
        $location = mysqli_real_escape_string($conn, $_POST['meeting_location']);
        
        $item_res = mysqli_query($conn, "SELECT item_name, item_price FROM items WHERE item_id = $item_id LIMIT 1");
        if ($item_data = mysqli_fetch_assoc($item_res)) {
            $item_name = mysqli_real_escape_string($conn, $item_data['item_name']);
            $price = floatval($item_data['item_price']);
            $deal_type = ($price == 0) ? 'donation' : 'purchase';
            
            // Insert into your orders table
            $order_sql = "INSERT INTO orders (item_id, buyer_id, seller_id, deal_type, price, meeting_location, payment_method, status) 
                          VALUES ($item_id, $buyer_id, $session_user_id, '$deal_type', $price, '$location', '', 'pending')";
            
            if (mysqli_query($conn, $order_sql)) {
                $generated_order_id = mysqli_insert_id($conn);
                
                // Post the Official Card message into the chat stream
                $msg_text = "Sent an Official Deal Proposal for: " . $item_name;
                $msg_sql = "INSERT INTO messages (sender_id, receiver_id, message_text, context_item, order_id) 
                            VALUES ('$session_user_id', '$buyer_id', '$msg_text', '$item_name', $generated_order_id)";
                mysqli_query($conn, $msg_sql);
            }
        }
        header("Location: messaging.php?to_id=" . $buyer_id);
        exit();
    }
}

header("Location: messaging.php");
exit();