<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit;
}

$currentUserId = getCurrentUserId();
$partnerId = (int)($_GET['user'] ?? 0);

if (!$partnerId) {
    echo json_encode([]);
    exit;
}

$db = getDB();
$stmt = $db->prepare("
    SELECT m.*, u.username AS sender_username
    FROM Messages m
    JOIN Users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?)
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.sent_at ASC
");
$stmt->execute([$currentUserId, $partnerId, $partnerId, $currentUserId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
