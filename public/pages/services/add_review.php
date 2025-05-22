<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$db = getDB();

$userId = getCurrentUserID();
$serviceId = $_POST['service_id'] ?? null;
$comment = trim($_POST['review_text'] ?? '');
$rating = intval($_POST['rating'] ?? 0);

if (!$serviceId || !$comment || $rating < 1 || $rating > 5) {
    die('Invalid input.');
}

$stmt = $db->prepare("SELECT freelancer_id FROM Services WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die('Service not found.');
}

if ($service['freelancer_id'] == $userId) {
    die('You cannot review your own service.');
}

$stmt = $db->prepare("INSERT INTO Reviews (service_id, client_id, rating, comment, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
$stmt->execute([$serviceId, $userId, $rating, $comment]);

header('Location: ' . SITE_URL . '/pages/services/view.php?id=' . $serviceId . '&review=success');
exit;
