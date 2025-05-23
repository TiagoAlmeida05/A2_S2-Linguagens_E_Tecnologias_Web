<?php
// pages/services/paid_services.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/user.php';

session_start();
$userId = getCurrentUserId();
if (!$userId) {
    redirectToLogin();
    exit;
}

$db = getDB();

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_tx_id'])) {
    $txId = (int)$_POST['cancel_tx_id'];
    $stmt = $db->prepare(
        "UPDATE Transactions SET status = 'cancelled' WHERE id = ? AND client_id = ?"
    );
    $stmt->execute([$txId, $userId]);
    $_SESSION['success'] = 'Transaction cancelled.';
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Fetch all services this user has paid for
$stmt = $db->prepare(
    "SELECT
        s.*,
        c.name       AS category_name,
        u.username   AS freelancer_name,
        t.id         AS transaction_id,
        t.status     AS transaction_status,
        t.created_at AS purchased_at
    FROM Transactions t
    JOIN Services   s ON t.service_id   = s.id
    JOIN Categories c ON s.category_id  = c.id
    JOIN Users      u ON s.freelancer_id = u.id
    WHERE t.client_id = ?
    ORDER BY t.created_at DESC"
);
$stmt->execute([$userId]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/../../templates/header.php';
?>

<div class="featured-services">
  <div class="container">
    <h1 class="gradient-heading">Paid Services</h1>

    <div class="search-bar">
      <input type="text" id="serviceSearch" placeholder="Search services by title...">
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
          $cats = $db->query("SELECT id, name FROM Categories")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($cats as $cat) {
              echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
          }
          ?>
        </select>
      </div>
    </form>

    <?php if (empty($services)): ?>
      <p>You haven't purchased any services yet.</p>
    <?php else: ?>
      <div class="services-grid">
        <?php foreach ($services as $s): ?>
          <div class="service-card"
               data-title="<?php echo htmlspecialchars($s['title']); ?>"
               data-price="<?php echo htmlspecialchars($s['price']); ?>"
               data-category="<?php echo htmlspecialchars($s['category_id']); ?>">
            <h3><?php echo htmlspecialchars($s['title']); ?></h3>
            <p class="service-category"><?php echo htmlspecialchars($s['category_name']); ?></p>
            <p class="service-price">$<?php echo htmlspecialchars($s['price']); ?></p>
            <p class="service-freelancer">by <?php echo htmlspecialchars($s['freelancer_name']); ?></p>
            <p class="transaction-meta">
              <small>Status: <?php echo htmlspecialchars(ucfirst($s['transaction_status'])); ?></small><br>
              <small>Purchased: <?php echo date("F j, Y", strtotime($s['purchased_at'])); ?></small>
            </p>

            <a href="<?php echo SITE_URL; ?>/pages/services/view.php?id=<?php echo $s['id']; ?>" class="btn">View Details</a>

            <?php if ($s['transaction_status'] !== 'cancelled'): ?>
              <div style="text-align:center; margin-top:0.5rem;">
                <form method="POST">
                  <input type="hidden" name="cancel_tx_id" value="<?php echo $s['transaction_id']; ?>">
                  <button type="submit" class="btn btn-danger">Cancel Transaction</button>
                </form>
              </div>
            <?php else: ?>
              <div style="text-align:center; margin-top:0.5rem;">
                <span style="color:#dc3545; font-weight:600;">Cancelled</span>
              </div>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/search.js"></script>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>