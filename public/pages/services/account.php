<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

$db = getDB();

// Get the freelancer ID from query string
$freelancerId = $_GET['user'] ?? null;

if (!$freelancerId) {
    displayError("No freelancer specified.");
    exit;
}

// Fetch freelancer info
$stmt = $db->prepare("SELECT id, name, username, email, created_at FROM Users WHERE id = ?");
$stmt->execute([$freelancerId]);
$freelancer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$freelancer) {
    displayError("Freelancer not found.");
    exit;
}

// Fetch freelancer's services
$stmt = $db->prepare("
    SELECT Services.*, Categories.name AS category_name 
    FROM Services
    JOIN Categories ON Services.category_id = Categories.id
    WHERE freelancer_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$freelancerId]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../../templates/header.php';
?>

<div class="profile-container">
    <h1 class="gradient-heading"><?php echo htmlspecialchars($freelancer['username']); ?>'s Profile</h1>
    
    <div class="freelancer-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($freelancer['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($freelancer['email']); ?></p>
            <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($freelancer['created_at'])); ?></p>
        </div>
    </div>
</div>

<div class="profile-container">
    <h2 class="gradient-heading">Services by <?php echo htmlspecialchars($freelancer['username']); ?></h2>

    <?php if (empty($services)): ?>
        <p>This freelancer has not listed any services yet.</p>
    <?php else: ?>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></p>
                    <p class="service-price">$<?php echo htmlspecialchars($service['price']); ?></p>
                    <a href="<?php echo SITE_URL; ?>/pages/services/view.php?id=<?php echo $service['id']; ?>" class="btn">View Service</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include_once '../../templates/footer.php'; ?>
