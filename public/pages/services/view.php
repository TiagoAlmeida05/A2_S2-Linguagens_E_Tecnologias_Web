<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

$serviceId = $_GET['id'];

$db = getDB();

// Fetch service details
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

    <a href="javascript:history.back()" class="btn">Back</a>
</div>

<?php include_once '../../templates/footer.php'; ?>
