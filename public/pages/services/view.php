<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    displayError("No service specified.");
    exit;
}

$db = getDB();

$stmt = $db->prepare("
    SELECT Services.*, Categories.name AS category_name, Users.username AS freelancer_name
    FROM Services
    JOIN Categories ON Services.category_id = Categories.id
    JOIN Users ON Services.freelancer_id = Users.id
    WHERE Services.id = ?
");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    displayError("Service not found.");
    exit;
}

$stmt2 = $db->prepare("SELECT file_path, type FROM ServiceMedia WHERE service_id = ?");
$stmt2->execute([$serviceId]);
$mediaFiles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$currentUserId = getCurrentUserId();
$isAdmin = false;

if ($currentUserId) {
    $stmt3 = $db->prepare("SELECT is_admin FROM Users WHERE id = ?");
    $stmt3->execute([$currentUserId]);
    $user = $stmt3->fetch(PDO::FETCH_ASSOC);
    $isAdmin = $user && $user['is_admin'] == 1;
}

include_once '../../templates/header.php';
?>

<div class="profile-container">
    <h1 class="gradient-heading"><?php echo htmlspecialchars($service['title']); ?></h1>
    <h3><strong>Created by
        <a href="<?php echo SITE_URL; ?>account.php?user=<?php echo $service['freelancer_id']; ?>">
            <?php echo htmlspecialchars($service['freelancer_name']); ?>
        </a>
    </strong></h3>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($service['category_name']); ?></p>
    <p><strong>Price:</strong> $<?php echo htmlspecialchars($service['price']); ?></p>
    <p><strong>Delivery Time:</strong> <?php echo htmlspecialchars($service['delivery_time']); ?> days</p>
    <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>

    <?php if (!empty($mediaFiles)): ?>
        <div class="media-preview">
            <h4>Media:</h4>
            <?php foreach ($mediaFiles as $media): ?>
                <?php if ($media['type'] === 'image'): ?>
                    <img src="<?php echo SITE_URL . '/uploads/' . htmlspecialchars($media['file_path']); ?>" 
                         alt="Service Image" 
                         style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 10px;">
                <?php elseif ($media['type'] === 'video'): ?>
                    <video controls style="max-width: 100%; border-radius: 8px; margin-bottom: 10px;">
                        <source src="<?php echo SITE_URL . '/uploads/' . htmlspecialchars($media['file_path']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($currentUserId === $service['freelancer_id'] || $isAdmin): ?>
        <form action="delete_service.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this service?');" style="margin-top: 20px;">
            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
            <button type="submit" class="btn-delete-service">Delete Service</button>
        </form>
    <?php endif; ?>

    <a href="javascript:history.back()" class="btn">Back</a>
</div>

<?php include_once '../../templates/footer.php'; ?>
