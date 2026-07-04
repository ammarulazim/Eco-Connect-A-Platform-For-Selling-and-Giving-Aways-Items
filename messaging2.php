<?php
include 'database.php';

// Route guard: Redirect to login if user isn't authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// 1. HANDLE OUTGOING MESSAGE SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = mysqli_real_escape_string($conn, $_POST['receiver_id']);
    $message_text = mysqli_real_escape_string($conn, $_POST['message_text']);
    $context_item = isset($_POST['context_item']) ? mysqli_real_escape_string($conn, $_POST['context_item']) : '';

    if (!empty($message_text)) {

        // 1. insert message
        $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message_text, context_item) 
                    VALUES ('$current_user_id', '$receiver_id', '$message_text', '$context_item')";
        mysqli_query($conn, $insert_sql);

        // 2. get sender name
        $sender_sql = "SELECT username FROM users WHERE user_id = '$current_user_id'";
        $sender_res = mysqli_query($conn, $sender_sql);
        $sender_row = mysqli_fetch_assoc($sender_res);
        $sender_name = $sender_row['username'];

        // 3. short message preview
        $short_msg = substr($message_text, 0, 40);

        // 4. notification message (MORE CLEAR)
        $notif_text = "@$sender_name: \"$short_msg\"";

        // 5. INSERT NOTIFICATION (IMPORTANT FIX)
        $notif_sql = "INSERT INTO notifications (user_id, type, message, related_id, is_read)
                    VALUES ('$receiver_id', 'message', '$notif_text', '$current_user_id', 0)";

        mysqli_query($conn, $notif_sql);

        header("Location: messaging.php?to_id=" . $receiver_id);
        exit();
    }
}

// 2. FETCH CHAT SIDEBAR LOGS (Distinct User Contacts)
// 2. FETCH ONLY ACTIVE CONVERSATIONS (REAL INBOX)
$sidebar_sql = "
SELECT 
    c.contact_id,
    u.username,
    u.profile_image,
    c.last_msg,
    c.last_time
FROM (
    SELECT 
        IF(sender_id = '$current_user_id', receiver_id, sender_id) AS contact_id,
        MAX(created_at) AS last_time,
        (
            SELECT message_text 
            FROM messages m2
            WHERE (
                (m2.sender_id = '$current_user_id' AND m2.receiver_id = IF(messages.sender_id = '$current_user_id', messages.receiver_id, messages.sender_id))
                OR
                (m2.receiver_id = '$current_user_id' AND m2.sender_id = IF(messages.sender_id = '$current_user_id', messages.receiver_id, messages.sender_id))
            )
            ORDER BY m2.created_at DESC
            LIMIT 1
        ) AS last_msg
    FROM messages
    WHERE sender_id = '$current_user_id' OR receiver_id = '$current_user_id'
    GROUP BY contact_id
) c
JOIN users u ON u.user_id = c.contact_id
ORDER BY c.last_time DESC
";

$sidebar_result = mysqli_query($conn, $sidebar_sql);

// 3. CAPTURE ACTIVE CONVERSATION FOCUS STATE
$current_user_id = $_SESSION['user_id'];

$active_chat_id = isset($_GET['to_id']) ? mysqli_real_escape_string($conn, $_GET['to_id']) : null;
$context_item_prefill = isset($_GET['item']) ? mysqli_real_escape_string($conn, $_GET['item']) : '';

$page_title = "Messages | Eco-Connect";
$page_css = "messaging.css";
include 'header.php';
?>

<main class="messaging-wrapper">
    <div class="messaging-layout-card">
        
        <aside class="chat-sidebar">
            <div class="sidebar-header">
                <h3><i class="fa-solid fa-inbox"></i> Inbox Conversations</h3>
            </div>
            
            <div class="contact-list-stream">
                <?php 
                if (mysqli_num_rows($sidebar_result) > 0):
                    while ($chat_row = mysqli_fetch_assoc($sidebar_result)):
                        $contact_id = $chat_row['contact_id'];
                        
                        // Fetch individual user accounts profiles metrics
                        $user_sql = "SELECT username, profile_image FROM users WHERE user_id = '$contact_id'";
                        $user_res = mysqli_query($conn, $user_sql);
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
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="empty-inbox-state">
                        <i class="fa-regular fa-comment-dots"></i>
                        <p>No chat history yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <section class="chat-main-stage">
            <?php 
            if ($active_chat_id):
                // Fetch current chat focal party profile data attributes
                $target_profile_sql = "SELECT username, profile_image FROM users WHERE user_id = '$active_chat_id'";
                $target_profile_res = mysqli_query($conn, $target_profile_sql);
                $target_user = mysqli_fetch_assoc($target_profile_res);

                // Fetch conversation stream log rows
                $stream_sql = "SELECT * FROM messages 
                               WHERE (sender_id = '$current_user_id' AND receiver_id = '$active_chat_id') 
                                  OR (sender_id = '$active_chat_id' AND receiver_id = '$current_user_id') 
                               ORDER BY created_at ASC";
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
                                <?php if (!empty($msg['context_item']) && !$is_sender): ?>
                                    <div class="bubble-context-header">
                                        <i class="fa-solid fa-circle-info"></i> Regarding: <?php echo htmlspecialchars($msg['context_item']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="message-bubble">
                                    <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                </div>
                                <span class="bubble-timestamp"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <form method="POST" class="chat-input-bar">
                    <input type="hidden" name="receiver_id" value="<?php echo $active_chat_id; ?>">
                    <input type="hidden" name="context_item" value="<?php echo $context_item_prefill; ?>">
                    
                    <input type="text" name="message_text" placeholder="Type your message here..." required autocomplete="off" class="text-input-field">
                    <button type="submit" name="send_message" class="send-message-btn">
                        <i class="fa-solid fa-paper-plane"></i> Send
                    </button>
                </form>

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

<script>
// Auto scroll container log windows layout to absolute bottom to prioritize latest messages
const canvas = document.getElementById('chatMessageCanvas');
if (canvas) {
    canvas.scrollTop = canvas.scrollHeight;
}
</script>

<?php include 'footer.php'; ?>