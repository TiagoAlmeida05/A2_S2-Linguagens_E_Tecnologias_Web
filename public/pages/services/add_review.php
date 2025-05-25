<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

$serviceId = $_GET['service_id'] ?? null;
if (!$serviceId) {
    displayError("No service specified.");
    exit;
}

$db = getDB();
$currentUserId = getCurrentUserID();
if (!$currentUserId) {
    redirect(SITE_URL . "/pages/login.php");
    exit;
}

$stmt = $db->prepare("SELECT freelancer_id FROM Services WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    displayError("Service not found.");
    exit;
}

if ($service['freelancer_id'] == $currentUserId) {
    displayError("You can't review your own service.");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewText = trim($_POST['review_text']);
    $rating = (int)$_POST['rating'];

    if ($rating < 1 || $rating > 5 || empty($reviewText)) {
        $error = "Please provide a rating between 1 and 5 and a comment.";
    } else {
        $stmt = $db->prepare("
            INSERT INTO Reviews (service_id, client_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$serviceId, $currentUserId, $rating, $reviewText]);
        redirect(SITE_URL . "/pages/services/view.php?id=" . $serviceId);
        exit;
    }
}

include_once '../../templates/header.php';
?>

<div class="review-form">
    <h2>Leave a Review</h2>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="review_text">Your Review</label>
            <textarea name="review_text" id="review_text" class="form-control" required placeholder="Write your review here..."></textarea>
        </div>

        <div class="form-group">
            <label for="rating">Rating</label>
            <select name="rating" id="rating" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> stars</option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit">Submit Review</button>
    </form>

    <a href="<?php echo SITE_URL; ?>/pages/services/view.php?id=<?php echo $serviceId; ?>" class="btn" style="margin-top: 1rem;">
        Back to Service
    </a>
</div>

<?php include_once '../../templates/footer.php'; ?>