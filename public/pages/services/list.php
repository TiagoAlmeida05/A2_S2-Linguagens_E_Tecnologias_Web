<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

$db = getDB();

// Get optional query parameters
$userId = $_GET['user'] ?? null;
$categoryId = $_GET['category'] ?? null;

// Build base query
$query = "
    SELECT Services.*, Categories.name AS category_name, Users.username AS freelancer_name
    FROM Services
    JOIN Categories ON Services.category_id = Categories.id
    JOIN Users ON Services.freelancer_id = Users.id
";

// Apply filters if present
$conditions = [];
$params = [];

if ($userId) {
    $conditions[] = "freelancer_id = ?";
    $params[] = $userId;
}

if ($categoryId) {
    $conditions[] = "category_id = ?";
    $params[] = $categoryId;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Order by newest
$query .= " ORDER BY Services.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../../templates/header.php';
?>

<div class="featured-services">
    <div class="container">
        <?php 
        if ($userId) {
            echo '<h1 class="gradient-heading">Your Services</h1';
        } elseif ($categoryId) {
            // Fetch category name
            $stmt = $db->prepare("SELECT name FROM Categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($category) {
                echo '<h1 class="gradient-heading">' . htmlspecialchars($category['name']) . "</h1>";
            }
        }
        ?>
        <?php if (empty($services)): ?>
            <p>No services available yet. <?php if (!isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>/pages/register.php">Register</a> and be the first to offer your services!
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/pages/services/add_job.php">Create a service</a> and be the first to offer your expertise!
            <?php endif; ?></p>
        <?php else: ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></p>
                        <p class="service-price">$<?php echo htmlspecialchars($service['price']); ?></p>
                        <p class="service-freelancer">by <?php echo htmlspecialchars($service['freelancer_name']); ?></p>
                        <?php if (isset($service['avg_rating'])): ?>
                            <p class="service-rating">Rating: <?php echo number_format($service['avg_rating'], 1); ?>/5</p>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/pages/services/view.php?id=<?php echo $service['id']; ?>" class="btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../templates/footer.php'; ?>
