<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$currentUserId = getCurrentUserId();
$data = json_decode(file_get_contents('php://input'), true);

$receiverId = isset($data['receiver_id']) ? (int)$data['receiver_id'] : 0;
$content = isset($data['content']) ? trim($data['content']) : '';

if (!$receiverId || $receiverId === $currentUserId || !$content) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("INSERT INTO Messages (sender_id, receiver_id, content, sent_at) VALUES (?, ?, ?, datetime('now'))");
$success = $stmt->execute([$currentUserId, $receiverId, $content]);

echo json_encode(['success' => $success]);
