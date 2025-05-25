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

        <?php
        // Add Sales section after services grid
        if ($showMine && $userId) {
            // Get all transactions for the freelancer's services
            $stmt = $db->prepare("
                SELECT t.*, s.title as service_title, s.price, u.username as client_name, u.email as client_email
                FROM Transactions t
                JOIN Services s ON t.service_id = s.id
                JOIN Users u ON t.client_id = u.id
                WHERE s.freelancer_id = ?
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$userId]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($transactions)) {
                echo '<h2 class="gradient-heading">Sales</h2>';
                echo '<div class="transactions-grid">';
                foreach ($transactions as $transaction) {
                    $statusClass = $transaction['status'] === 'completed' ? 'completed' : 'pending';
                    ?>
                    <div class="transaction-card <?php echo $statusClass; ?>">
                        <div class="transaction-status <?php echo $statusClass; ?>">
                            <?php echo ucfirst($transaction['status']); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($transaction['service_title']); ?></h3>
                        <p class="transaction-client">Client: <?php echo htmlspecialchars($transaction['client_name']); ?></p>
                        <p class="transaction-email">Email: <?php echo htmlspecialchars($transaction['client_email']); ?></p>
                        <p class="transaction-price">Price: $<?php echo htmlspecialchars($transaction['price']); ?></p>
                        <p class="transaction-date">Ordered: <?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></p>
                        <?php if ($transaction['status'] === 'pending'): ?>
                            <form action="<?php echo SITE_URL; ?>/pages/services/complete_transaction.php" method="POST" class="transaction-actions">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                <button type="submit" class="btn btn-success">Mark as Completed</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                echo '</div>';
            }
        }
        ?>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/search.js"></script>
<?php include_once '../../templates/footer.php'; ?>
