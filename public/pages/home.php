<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

// Get featured services
function getFeaturedServices($limit = 6) {
    try {
        $db = getDB();
        
        $query = "SELECT s.*, u.username as freelancer_name, c.name as category_name,
                 (SELECT AVG(rating) FROM Reviews WHERE service_id = s.id) as avg_rating
                 FROM Services s
                 JOIN Users u ON s.freelancer_id = u.id
                 JOIN Categories c ON s.category_id = c.id
                 ORDER BY s.created_at DESC
                 LIMIT ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Get categories
function getCategories() {
    try {
        $db = getDB();
        
        $query = "SELECT * FROM Categories ORDER BY name";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

$featuredServices = getFeaturedServices();
$categories = getCategories();

// Include header
include_once '../templates/header.php';
?>

<!-- Close the container from header -->
</div>

<div class="hero">
    <div class="container">
        <h1>Find the perfect freelance services for your business</h1>
        <p>Hire skilled freelancers or offer your expert services to clients around the world.</p>
        <form action="<?php echo SITE_URL; ?>/pages/services/list.php" method="GET" class="search-bar">
            <input type="text" id="serviceSearch" name="search" placeholder="Search for services...">
        </form>
    </div>
</div>

<div class="categories-section">
    <div class="container">
        <h2>Popular Categories</h2>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo SITE_URL; ?>/pages/services/list.php?category=<?php echo $category['id']; ?>" class="category-card">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description'] ?? ''); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="featured-services">
    <div class="container">
        <h2>Featured Services</h2>
        
        <?php if (empty($featuredServices)): ?>
            <p>No services available yet. <?php if (!isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>/pages/register.php">Register</a> and be the first to offer your services!
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/pages/services/create.php">Create a service</a> and be the first to offer your expertise!
            <?php endif; ?></p>
        <?php else: ?>
            <div class="services-grid">
                <?php foreach ($featuredServices as $service): ?>
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

<div class="cta-section">
    <div class="container">
        <h2>Ready to start?</h2>
        <p>Join our community of freelancers and clients today.</p>
        
        <?php if (!isLoggedIn()): ?>
            <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn">Get Started</a>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/pages/services/list.php" class="btn">Browse Services</a>
        <?php endif; ?>
    </div>
</div>

<!-- Open container again for footer -->
<div class="container">
<script src="<?php echo SITE_URL; ?>/assets/js/search.js"></script>
<?php include_once '../templates/footer.php'; ?> 