<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    displayError("Invalid request method.");
    exit;
}

$serviceId = $_POST['service_id'] ?? null;
if (!$serviceId) {
    displayError("No service specified.");
    exit;
}

$currentUserId = getCurrentUserID();
if (!$currentUserId) {
    displayError("You must be logged in to delete a service.");
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT freelancer_id FROM Services WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    displayError("Service not found.");
    exit;
}

$userStmt = $db->prepare("SELECT is_admin FROM Users WHERE id = ?");
$userStmt->execute([$currentUserId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = $user && isset($user['is_admin']) && $user['is_admin'] == 1;

if ($currentUserId != $service['freelancer_id'] && !$isAdmin) {
    displayError("You do not have permission to delete this service.");
    exit;
}

try {
    $db->beginTransaction();

    $mediaStmt = $db->prepare("SELECT file_path FROM ServiceMedia WHERE service_id = ?");
    $mediaStmt->execute([$serviceId]);
    $mediaFiles = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mediaFiles as $media) {
        $filePath = __DIR__ . '/../../uploads/' . $media['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $delMediaStmt = $db->prepare("DELETE FROM ServiceMedia WHERE service_id = ?");
    $delMediaStmt->execute([$serviceId]);

    $delServiceStmt = $db->prepare("DELETE FROM Services WHERE id = ?");
    $delServiceStmt->execute([$serviceId]);

    $db->commit();

    header("Location: /pages/home.php?msg=Service+deleted+successfully");
    exit;
} catch (Exception $e) {
    $db->rollBack();
    displayError("Failed to delete service: " . $e->getMessage());
}
