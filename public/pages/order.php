<?php
require_once '../includes/config.php';
require_once '../includes/user.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$currentUserId = getCurrentUserId();
$serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
if (!$serviceId) {
    die('Invalid service ID.');
}

$db = getDB();

$stmt = $db->prepare("SELECT * FROM Services WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die('Service not found.');
}

$stmt = $db->prepare("INSERT INTO Transactions (client_id, service_id, status) VALUES (?, ?, 'pending')");
$stmt->execute([$currentUserId, $serviceId]);
$transactionId = $db->lastInsertId();

header("Location: payment.php?transaction_id=$transactionId");
exit;

?>