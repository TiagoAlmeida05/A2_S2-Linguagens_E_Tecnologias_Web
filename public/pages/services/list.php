<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

$db = getDB();

$userId = getCurrentUserID() ?? null;
$categoryId = $_GET['category'] ?? null;
$showMine = isset($_GET['mine']) && $_GET['mine'] == 1;
$searchQuery = $_GET['search'] ?? '';

$query = "
    SELECT Services.*, Categories.name AS category_name, Users.username AS freelancer_name
    FROM Services
    JOIN Categories ON Services.category_id = Categories.id
    JOIN Users ON Services.freelancer_id = Users.id
";

$conditions = [];
$params = [];

if ($categoryId) {
    $conditions[] = "category_id = ?";
    $params[] = $categoryId;
} elseif ($showMine && $userId) {
    $conditions[] = "freelancer_id = ?";
    $params[] = $userId;
}

if ($searchQuery) {
    $conditions[] = "Services.title LIKE ?";
    $params[] = "%$searchQuery%";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../../templates/header.php';
?>

<div class="featured-services">
    <div class="container">

        <?php 
        if ($categoryId) {
            $stmt = $db->prepare("SELECT name FROM Categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($category) {
                echo '<h1 class="gradient-heading">' . htmlspecialchars($category['name']) . '</h1>';
                ?>
                <div class="search-bar">
                    <input type="text" id="serviceSearch" placeholder="Search services by title..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>

                <form id="filterForm" class="service-filters">
                    <div>
                        <label for="min_price">Min Price:</label>
                        <input type="number" id="min_price" placeholder="0">
                    </div>

                    <div>
                        <label for="max_price">Max Price:</label>
                        <input type="number" id="max_price" placeholder="999">
                    </div>

                    <div>
                        <label for="order">Order By Price:</label>
                        <select id="order">
                            <option value="">-- Select --</option>
                            <option value="asc">Low to High</option>
                            <option value="desc">High to Low</option>
                        </select>
                    </div>
                </form>
                <?php
            }
        } elseif ($showMine && $userId) {
            echo '<h1 class="gradient-heading">My Services</h1>';
            ?>
            <div class="search-bar">
                <input type="text" id="serviceSearch" placeholder="Search services by title..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>

            <form id="filterForm" class="service-filters">
                <div>
                    <label for="min_price">Min Price:</label>
                    <input type="number" id="min_price" placeholder="0">
                </div>

                <div>
                    <label for="max_price">Max Price:</label>
                    <input type="number" id="max_price" placeholder="999">
                </div>

                <div>
                    <label for="order">Order By Price:</label>
                    <select id="order">
                        <option value="">-- Select --</option>
                        <option value="asc">Low to High</option>
                        <option value="desc">High to Low</option>
                    </select>
                </div>

                <div>
                    <label for="category">Category:</label>
                    <select id="category">
                        <option value="">-- All --</option>
                        <?php
                        $stmt = $db->query("SELECT id, name FROM Categories");
                        while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$cat['id']}'>" . htmlspecialchars($cat['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </form>
            <?php
        } else {
            echo '<h1 class="gradient-heading">All Services</h1>';
            ?>
            <div class="search-bar">
                <input type="text" id="serviceSearch" placeholder="Search services by title..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>

            <form id="filterForm" class="service-filters">
                <div>
                    <label for="min_price">Min Price:</label>
                    <input type="number" id="min_price" placeholder="0">
                </div>

                <div>
                    <label for="max_price">Max Price:</label>
                    <input type="number" id="max_price" placeholder="999">
                </div>

                <div>
                    <label for="order">Order By Price:</label>
                    <select id="order">
                        <option value="">-- Select --</option>
                        <option value="asc">Low to High</option>
                        <option value="desc">High to Low</option>
                    </select>
                </div>

                <div>
                    <label for="category">Category:</label>
                    <select id="category">
                        <option value="">-- All --</option>
                        <?php
                        $stmt = $db->query("SELECT id, name FROM Categories");
                        while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$cat['id']}'>" . htmlspecialchars($cat['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </form>
            <?php
        }
        ?>

        <?php if (empty($services)): ?>
            <p>No services available yet. 
                <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/pages/register.php">Register</a> and be the first to offer your services!
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/pages/services/add_job.php">Create a service</a> and be the first to offer your expertise!
                <?php endif; ?>
            </p>
        <?php else: ?>

            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card"
                         data-title="<?php echo htmlspecialchars($service['title']); ?>"
                         data-price="<?php echo htmlspecialchars($service['price']); ?>"
                         data-category="<?php echo htmlspecialchars($service['category_id']); ?>">
                        <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></p>
                        <p class="service-price">$<?php echo htmlspecialchars($service['price']); ?></p>
                        <p class="service-freelancer">by <?php echo htmlspecialchars($service['freelancer_name']); ?></p>
                        <a href="<?php echo SITE_URL; ?>/pages/services/view.php?id=<?php echo $service['id']; ?>" class="btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/search.js"></script>
<?php include_once '../../templates/footer.php'; ?>
