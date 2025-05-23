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
    http_response_code(400);
    exit;
}

$db = getDB();
$stmt = $db->prepare("
  UPDATE Messages
  SET is_read = 1
  WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
");
$stmt->execute([$partnerId, $currentUserId]);

echo json_encode(['success' => true]);