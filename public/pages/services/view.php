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

// Load service
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

// Load media
$stmt = $db->prepare("SELECT file_path, type FROM ServiceMedia WHERE service_id = ?");
$stmt->execute([$serviceId]);
$mediaFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Current user & admin check
$currentUserId = getCurrentUserId();
$isAdmin = false;
if ($currentUserId) {
    $stmt = $db->prepare("SELECT is_admin FROM Users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $user    = $stmt->fetch(PDO::FETCH_ASSOC);
    $isAdmin = ($user && $user['is_admin'] == 1);
}
$stmtReviews = $db->prepare("
    SELECT Reviews.*, Users.username AS client_name
    FROM Reviews
    JOIN Users ON Reviews.client_id = Users.id
    WHERE Reviews.service_id = ?
    ORDER BY Reviews.created_at DESC
");
$stmtReviews->execute([$serviceId]);
$reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

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

        <a href="<?php echo SITE_URL; ?>/pages/services/edit_job.php?id=<?php echo $service['id']; ?>" class="btn">Edit Service</a>
    <?php endif; ?>

    <?php if ($currentUserId && $currentUserId !== $service['freelancer_id']): ?>
        <a href="<?php echo SITE_URL; ?>/pages/services/add_review.php?service_id=<?php echo $service['id']; ?>" class="btn">Leave a Review</a>
        <a href="/pages/messages.php?user=<?php echo $service['freelancer_id']; ?>"
           class="btn btn-primary"
           style="margin-left:10px;">
            Message Seller
        </a>
        <a href="pay_service.php?id=<?= $serviceId ?>" class="btn btn-success" style="margin-left:10px;">
            Pay Now
        </a>
    <?php endif; ?>

    <a href="javascript:history.back()" class="btn">Back</a>
</div>

<?php if ($currentUserId === $service['freelancer_id']): ?>
    <p style="text-align:center; margin: 2rem auto; max-width: 600px;">You cannot review your own service.</p>
<?php endif; ?>

<div class="service-reviews container">
    <h3>Reviews</h3>
    <?php if (empty($reviews)): ?>
        <p>No reviews yet. Be the first to leave one!</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <p class="review-meta">
                    <strong><?php echo htmlspecialchars($review['client_name']); ?></strong>
                    rated it 
                    <strong><?php echo $review['rating']; ?>/5</strong>
                    on 
                    <?php echo date("F j, Y", strtotime($review['created_at'])); ?>
                </p>
                <?php if (!empty($review['comment'])): ?>
                    <p class="review-comment">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include_once '../../templates/footer.php';
?>
