<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$currentUserId = getCurrentUserId();
$prefilledUserId = isset($_GET['user']) ? (int)$_GET['user'] : null;

$db = getDB();

$stmt = $db->prepare("
    SELECT u.id, u.username,
        SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count,
        MAX(m.sent_at) AS last_message_time
    FROM Users u
    JOIN Messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE u.id != ? AND (m.sender_id = ? OR m.receiver_id = ?)
    GROUP BY u.id, u.username
    ORDER BY last_message_time DESC
");
$stmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtNew = $db->prepare("
    SELECT id, username 
    FROM Users 
    WHERE id != ? 
    AND id NOT IN (
        SELECT DISTINCT u.id 
        FROM Users u
        JOIN Messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
        WHERE u.id != ? AND (m.sender_id = ? OR m.receiver_id = ?)
    )
    ORDER BY username
");
$stmtNew->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId]);
$newUsers = $stmtNew->fetchAll(PDO::FETCH_ASSOC);

include_once '../templates/header.php';
?>

<div class="messages-board">
    <div class="users-sidebar">
        <h3>Conversations</h3>
        <ul id="convo-list">
        <?php foreach ($conversations as $user): ?>
            <li>
            <a href="#" class="user-link" data-id="<?php echo $user['id']; ?>">
                <?php echo htmlspecialchars($user['username']); ?>
                <?php if ($user['unread_count'] > 0): ?>
                <span class="badge"><?php echo $user['unread_count']; ?></span>
                <?php endif; ?>
            </a>
            </li>
        <?php endforeach; ?>
        </ul>

        <button id="start-new-btn">Start New Conversation</button>
        <div id="new-user-list" style="display:none;">
            <div id="new-user-search">
                <input type="text" placeholder="Search users..." id="user-search-input">
            </div>
            <ul>
                <?php foreach (array_slice($newUsers, 0, 30) as $user): ?>
                    <li>
                        <a href="#" class="user-link" data-id="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="conversation-panel">
        <h3 id="conversation-title">Select a conversation</h3>
        <div class="messages-list" id="messages-list"></div>
        <form id="message-form" style="display:none;">
            <textarea name="content" id="message-content" placeholder="Type your message..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<script>
  window.currentUserId = <?php echo json_encode($currentUserId); ?>;
  window.prefilledUserId = <?php echo json_encode($prefilledUserId); ?>;
</script>
<script src="../assets/js/messages.js" defer></script>

<?php include_once '../templates/footer.php'; ?>
