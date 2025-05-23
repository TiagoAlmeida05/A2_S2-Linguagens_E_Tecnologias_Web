<!-- view.php -->
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

include_once '../../templates/header.php';
?>

<div class="profile-container">
    <h1 class="gradient-heading"><?= htmlspecialchars($service['title']) ?></h1>
    <h3>
        <strong>Created by 
            <a href="<?= SITE_URL ?>/account.php?user=<?= $service['freelancer_id'] ?>">
                <?= htmlspecialchars($service['freelancer_name']) ?>
            </a>
        </strong>
    </h3>

    <p><strong>Category:</strong> <?= htmlspecialchars($service['category_name']) ?></p>
    <p><strong>Price:</strong> $<?= htmlspecialchars($service['price']) ?></p>
    <p><strong>Delivery Time:</strong> <?= htmlspecialchars($service['delivery_time']) ?> days</p>
    <p><strong>Description:</strong><br>
        <?= nl2br(htmlspecialchars($service['description'])) ?>
    </p>

    <?php if (! empty($mediaFiles)): ?>
        <div class="media-preview">
            <h4>Media:</h4>
            <?php foreach ($mediaFiles as $media): ?>
                <?php if ($media['type'] === 'image'): ?>
                    <img src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($media['file_path']) ?>"
                         alt="Service Image"
                         style="max-width:100%;border-radius:8px;margin-bottom:10px;">
                <?php else: ?>
                    <video controls style="max-width:100%;border-radius:8px;margin-bottom:10px;">
                        <source src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($media['file_path']) ?>"
                                type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($currentUserId === $service['freelancer_id'] || $isAdmin): ?>
        <form action="delete_service.php" method="POST"
              onsubmit="return confirm('Delete this service?');"
              style="margin-top:20px;">
            <input type="hidden" name="service_id" value="<?= $serviceId ?>">
            <button type="submit" class="btn-delete-service">Delete Service</button>
        </form>
        <a href="<?= SITE_URL ?>/pages/services/edit_job.php?id=<?= $serviceId ?>" class="btn">
            Edit Service
        </a>
    <?php endif; ?>

    <a href="javascript:history.back()" class="btn">Back</a>

    <?php if ($currentUserId && $currentUserId !== $service['freelancer_id']): ?>
        <a href="chat.php?user_id=<?= $service['freelancer_id'] ?>&service_id=<?= $serviceId ?>"
           class="btn btn-primary"
           style="margin-left:10px;">
            Message Seller
        </a>
        <a href="pay_service.php?id=<?= $serviceId ?>" class="btn btn-success" style="margin-left:10px;">
            Pay Now
        </a>
    <?php endif; ?>
</div>

<?php if ($currentUserId && $currentUserId !== $service['freelancer_id']): ?>
    <div class="review-form" style="margin-top:2rem;">
        <h3>Leave a Review</h3>
        <form action="add_review.php" method="POST">
            <input type="hidden" name="service_id" value="<?= $serviceId ?>">
            <label for="review_text">Your Review</label>
            <textarea name="review_text" id="review_text" rows="4" required
                      placeholder="Write your review here..."></textarea>
            <label for="rating">Rating</label>
            <select name="rating" id="rating" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> stars</option>
                <?php endfor; ?>
            </select>
            <button type="submit">Submit Review</button>
        </form>
    </div>
<?php elseif ($currentUserId === $service['freelancer_id']): ?>
    <p style="text-align:center;margin:2rem auto;max-width:600px;">
        You cannot review your own service.
    </p>
<?php endif; ?>

<?php
// Load and display reviews
$stmt = $db->prepare("
    SELECT Reviews.*, Users.username AS client_name
    FROM Reviews
    JOIN Users ON Reviews.client_id = Users.id
    WHERE Reviews.service_id = ?
    ORDER BY Reviews.created_at DESC
");
$stmt->execute([$serviceId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (! ($currentUserId === $service['freelancer_id'] && empty($reviews))):
?>
    <div class="service-reviews container" style="margin-top:2rem;">
        <h3>Reviews</h3>
        <?php if (empty($reviews)): ?>
            <p>No reviews yet. Be the first to leave one!</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <p class="review-meta">
                        <strong><?= htmlspecialchars($review['client_name']) ?></strong>
                        rated it <strong><?= $review['rating'] ?>/5</strong>
                        on <?= date("F j, Y", strtotime($review['created_at'])) ?>
                    </p>
                    <?php if (! empty($review['comment'])): ?>
                        <p class="review-comment">"<?= htmlspecialchars($review['comment']) ?>"</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php
endif;

include_once '../../templates/footer.php';
?>
