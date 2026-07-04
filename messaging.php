<?php
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// 1. HANDLE OUTGOING STANDARD MESSAGE SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = mysqli_real_escape_string($conn, $_POST['receiver_id']);
    $message_text = mysqli_real_escape_string($conn, $_POST['message_text']);
    $context_item = isset($_POST['context_item']) ? mysqli_real_escape_string($conn, $_POST['context_item']) : '';

    if (!empty($message_text)) {
        $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message_text, context_item) 
                       VALUES ('$current_user_id', '$receiver_id', '$message_text', '$context_item')";
        mysqli_query($conn, $insert_sql);

        // Standard notification handling logic...
        header("Location: messaging.php?to_id=" . $receiver_id);
        exit();
    }
}

// 2. FETCH CHAT SIDEBAR CONVERSATIONS LOGS
$sidebar_sql = "
SELECT 
    c.contact_id, u.username, u.profile_image, c.last_msg, c.last_time
FROM (
    SELECT 
        IF(sender_id = '$current_user_id', receiver_id, sender_id) AS contact_id,
        MAX(created_at) AS last_time,
        (
            SELECT message_text FROM messages m2
            WHERE ((m2.sender_id = '$current_user_id' AND m2.receiver_id = IF(messages.sender_id = '$current_user_id', messages.receiver_id, messages.sender_id))
               OR (m2.receiver_id = '$current_user_id' AND m2.sender_id = IF(messages.sender_id = '$current_user_id', messages.receiver_id, messages.sender_id)))
            ORDER BY m2.created_at DESC LIMIT 1
        ) AS last_msg
    FROM messages WHERE sender_id = '$current_user_id' OR receiver_id = '$current_user_id'
    GROUP BY contact_id
) c
JOIN users u ON u.user_id = c.contact_id
ORDER BY c.last_time DESC";
$sidebar_result = mysqli_query($conn, $sidebar_sql);

$active_chat_id = isset($_GET['to_id']) ? mysqli_real_escape_string($conn, $_GET['to_id']) : null;
$context_item_prefill = isset($_GET['item']) ? mysqli_real_escape_string($conn, $_GET['item']) : '';

// 3. FETCH CURRENTLY LOGGED-IN USER'S ACTIVE ITEMS ONLY (For Proposal Dropdown)
$my_items = [];
$my_items_query = mysqli_query($conn, "SELECT item_id, item_name, item_price, status FROM items WHERE user_id = '$current_user_id' AND status = 'available'");
while ($item_row = mysqli_fetch_assoc($my_items_query)) {
    $my_items[] = $item_row;
}
?>

<?php 
$page_title = "Messages | Eco-Connect";
$page_css = "messaging.css";
$page_script = "messaging.js";
include 'header.php'; 
?>

<main class="messaging-wrapper">
    <div class="messaging-layout-card">
        
        <aside class="chat-sidebar">
            <div class="sidebar-header">
                <h3><i class="fa-solid fa-inbox"></i> Inbox Conversations</h3>
            </div>
            <div class="contact-list-stream">
                <?php if (mysqli_num_rows($sidebar_result) > 0): ?>
                    <?php while ($chat_row = mysqli_fetch_assoc($sidebar_result)): 
                        $contact_id = $chat_row['contact_id'];
                        $user_res = mysqli_query($conn, "SELECT username, profile_image FROM users WHERE user_id = '$contact_id'");
                        $contact_user = mysqli_fetch_assoc($user_res);
                        if (!$contact_user) continue;

                        $is_active = ($active_chat_id == $contact_id) ? 'active-contact' : '';
                        $pfp = !empty($contact_user['profile_image']) && $contact_user['profile_image'] !== 'default_pic.jpg' 
                               ? 'images/uploads/' . $contact_user['username'] . '/' . $contact_user['profile_image'] 
                               : 'images/profile/default_pic.jpg';
                    ?>
                        <a href="messaging.php?to_id=<?php echo $contact_id; ?>" class="contact-card <?php echo $is_active; ?>">
                            <img src="<?php echo $pfp; ?>" alt="Avatar" class="contact-avatar">
                            <div class="contact-info">
                                <h4 class="contact-name">@<?php echo htmlspecialchars($contact_user['username']); ?></h4>
                                <p class="contact-preview-text"><?php echo htmlspecialchars(substr($chat_row['last_msg'] ?? '', 0, 35)) . '...'; ?></p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-inbox-state"><i class="fa-regular fa-comment-dots"></i><p>No chat history yet.</p></div>
                <?php endif; ?>
            </div>
        </aside>

        <section class="chat-main-stage">
            <?php if ($active_chat_id): 
                $target_res = mysqli_query($conn, "SELECT username FROM users WHERE user_id = '$active_chat_id'");
                $target_user = mysqli_fetch_assoc($target_res);

                $stream_sql = "SELECT m.*, o.deal_type, o.price, o.meeting_location, o.payment_method, o.status, o.bank_name, i.item_name AS real_item_name
                               FROM messages m 
                               LEFT JOIN orders o ON m.order_id = o.order_id
                               LEFT JOIN items i ON o.item_id = i.item_id
                               WHERE (m.sender_id = '$current_user_id' AND m.receiver_id = '$active_chat_id') 
                                  OR (m.sender_id = '$active_chat_id' AND m.receiver_id = '$current_user_id') 
                               ORDER BY m.created_at ASC";
                $stream_result = mysqli_query($conn, $stream_sql);
            ?>
                <div class="chat-header-banner">
                    <div class="target-user-meta">
                        <h4>Conversation with <span>@<?php echo htmlspecialchars($target_user['username']); ?></span></h4>
                    </div>
                    <?php if (!empty($context_item_prefill)): ?>
                        <div class="context-item-tag">
                            <i class="fa-solid fa-cart-shopping"></i> Inquiry: <strong><?php echo $context_item_prefill; ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="chat-bubble-canvas" id="chatMessageCanvas">
                    <?php while ($msg = mysqli_fetch_assoc($stream_result)): 
                        $is_sender = ($msg['sender_id'] == $current_user_id);
                    ?>
                        <div class="message-row <?php echo $is_sender ? 'sender-row' : 'receiver-row'; ?>">
                            <div class="bubble-wrapper">
                                
                                <?php if (!empty($msg['order_id'])): ?>
                                    <div class="order-card-message">
                                        <div class="order-card-header"><i class="fa-solid fa-signature"></i> Eco-Connect Order Offer</div>
                                        <h4><?php echo htmlspecialchars($msg['real_item_name'] ?? $msg['context_item']); ?></h4>
                                        <div class="order-details-list">
                                            <span><strong>Type:</strong> <?php echo ucfirst($msg['deal_type']); ?></span>
                                            <span><strong>Price:</strong> <?php echo ($msg['deal_type'] === 'donation' || $msg['price'] == 0) ? 'FREE' : 'RM ' . number_format($msg['price'], 2); ?></span>
                                            <span><strong>Location:</strong> <?php echo htmlspecialchars($msg['meeting_location']); ?></span>
                                        </div>
                                        
                                        <?php if ($msg['status'] === 'pending'): ?>
                                            <?php if (!$is_sender): ?>
                                                <div class="order-actions-row">
                                                    <?php if ($msg['deal_type'] === 'donation' || $msg['price'] == 0): ?>
                                                        <a href="messaging_action.php?order_id=<?php echo $msg['order_id']; ?>&status=accepted&method=Handover&to_id=<?php echo $active_chat_id; ?>" class="btn-order-accept" style="text-decoration:none; text-align:center;">Accept</a>
                                                    <?php else: ?>
                                                        <button class="btn-order-accept" onclick="openPaymentModal(<?php echo $msg['order_id']; ?>, <?php echo $msg['price']; ?>)">Proceed to Pay</button>
                                                    <?php endif; ?>
                                                    <a href="messaging_action.php?order_id=<?php echo $msg['order_id']; ?>&status=declined&to_id=<?php echo $active_chat_id; ?>" class="btn-order-decline" style="text-decoration:none; text-align:center;">Decline</a>
                                                </div>
                                            <?php else: ?>
                                                <div class="order-status-badge" style="background:rgba(255,255,255,0.05); color:#94a3b8;">Awaiting buyer checkout...</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="order-status-badge status-<?php echo $msg['status']; ?>">Order <?php echo ucfirst($msg['status']); ?></div>
                                        <?php endif; ?>
                                    </div>

                                <?php elseif (!empty($msg['context_item']) && is_numeric($msg['context_item'])): 
                                    // STEP 1 CARD: Buyer initiated Buy Intent
                                    $req_item_id = intval($msg['context_item']);
                                    $get_item = mysqli_query($conn, "SELECT item_name FROM items WHERE item_id = $req_item_id LIMIT 1");
                                    $item_name_data = mysqli_fetch_assoc($get_item);
                                    $display_name = $item_name_data['item_name'] ?? 'Unknown Item';
                                ?>
                                    <div class="order-card-message buy-request-card" style="border-left-color: #ffb703;">
                                        <div class="order-card-header" style="color: #ffb703;"><i class="fa-solid fa-cart-shopping"></i> Purchase Inquiry Request</div>
                                        <h4><?php echo htmlspecialchars($display_name); ?></h4>
                                        <p style="font-size:0.85rem; margin: 5px 0 10px 0; color:#d1dfd6;"><?php echo htmlspecialchars($msg['message_text']); ?></p>
                                        
                                        <?php if (!$is_sender): ?>
                                            <button class="btn-order-accept" style="background:#ffb703; color:#0d1a13; cursor:pointer;" onclick="triggerPreFilledProposal(<?php echo $req_item_id; ?>)">
                                                <i class="fa-solid fa-file-signature"></i> Review Request & Set Location
                                            </button>
                                        <?php else: ?>
                                            <div class="order-status-badge" style="background:rgba(255,183,3,0.1); color:#ffb703;">Request Sent to Owner</div>
                                        <?php endif; ?>
                                    </div>

                                <?php else: ?>
                                    <div class="message-bubble"><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></div>
                                <?php endif; ?>

                                <span class="bubble-timestamp"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="chat-input-bar">
                    <div class="action-trigger-container">
                        <button type="button" class="btn-chat-plus" id="chatPlusBtn"><i class="fa-solid fa-plus"></i></button>
                        <div class="chat-action-menu" id="chatActionMenu">
                            <button type="button" id="openOrderModalBtn"><i class="fa-solid fa-file-contract"></i> Propose Deal</button>
                        </div>
                    </div>

                    <form method="POST" class="message-form-element">
                        <input type="hidden" name="receiver_id" value="<?php echo $active_chat_id; ?>">
                        <input type="text" name="message_text" placeholder="Type message..." required autocomplete="off" class="text-input-field">
                        <button type="submit" name="send_message" class="send-message-btn"><i class="fa-solid fa-paper-plane"></i> Send</button>
                    </form>
                </div>

            <?php else: ?>
                <div class="blank-canvas-placeholder">
                    <i class="fa-regular fa-paper-plane"></i>
                    <h3>Welcome to Eco-Connect Chat</h3>
                    <p>Select a contact from your sidebar or find an item listing to start negotiating exchanges safely.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<div id="orderFormModal" class="order-modal-backdrop">
    <div class="order-modal-content">
        <span class="close-order-modal" id="closeOrderModalBtn">&times;</span>
        <h3><i class="fa-solid fa-file-invoice-dollar"></i> Propose Trade Order</h3>
        
        <form action="messaging_order.php" method="POST">
            <input type="hidden" name="owner_proposal" value="1">
            <input type="hidden" name="buyer_id" value="<?php echo $active_chat_id; ?>">

            <label>Selected Item Listing</label>
            <select name="item_id" id="modalItemSelectDropdown" required>
                <option value="">-- Choose Item --</option>
                <?php 
                $my_items_query = mysqli_query($conn, "SELECT item_id, item_name, item_price FROM items WHERE user_id = '$current_user_id' AND status = 'available'");
                if ($my_items_query && mysqli_num_rows($my_items_query) > 0) {
                    while ($itm = mysqli_fetch_assoc($my_items_query)) {
                        $price_label = ($itm['item_price'] == 0) ? 'Donation' : 'RM ' . number_format($itm['item_price'], 2);
                        echo '<option value="' . $itm['item_id'] . '">' . htmlspecialchars($itm['item_name']) . ' (' . $price_label . ')</option>';
                    }
                }
                ?>
            </select>

            <label>Meeting / Handover Location</label>
            <div class="input-box" style="position: relative; margin-bottom: 15px;">
                <input type="text" id="proposalLocationInput" name="meeting_location" placeholder="Enter meetup destination point..." required autocomplete="off" style="width: 100%;">
                <div id="proposalLocationSuggestions" class="location-suggestions-box"></div>
            </div>
            

            <button type="submit" class="btn-submit-order">Send Official Proposal to Buyer</button>
        </form>
    </div>
</div>

<div id="paymentModal" class="order-modal-backdrop">
    <div class="order-modal-content" style="max-width: 380px;">
        <span class="close-order-modal" onclick="closePaymentModal()">&times;</span>
        <h3><i class="fa-solid fa-credit-card"></i> Secure Payment Gateway</h3>
        <p style="font-size: 0.9rem; color: #8fa399; margin-bottom: 15px;">Total Due: <strong id="payAmountLabel" style="color: #9ec55e;">RM 0.00</strong></p>
        
        <form action="messaging_action.php" method="GET">
            <input type="hidden" name="order_id" id="payFormOrderId">
            <input type="hidden" name="status" value="accepted">
            <input type="hidden" name="to_id" value="<?php echo $active_chat_id; ?>">

            <label>Select Payment Gateway</label>
            <select name="method" id="paymentMethodSelect" onchange="toggleBankSelect()" required>
                <option value="Online Banking">Online Banking (FPX)</option>
                <option value="Cash on Delivery">Cash on Delivery (COD)</option>
            </select>

            <div id="bankDropdownGroup">
                <label>Select Bank Entity</label>
                <select name="bank_name" id="paymentBankSelect">
                    <option value="Maybank2u">Maybank (Maybank2u)</option>
                    <option value="CIMB Clicks">CIMB Bank (CIMB Clicks)</option>
                    <option value="Public Bank">Public Bank</option>
                    <option value="Rhb Now">RHB Bank</option>
                    <option value="Bank Islam">Bank Islam</option>
                </select>
            </div>

            <button type="submit" class="btn-submit-order" style="background: #9ec55e;">Authorize Transaction</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>