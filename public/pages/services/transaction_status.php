<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

$currentUserId = getCurrentUserId();
if (!$currentUserId) {
    redirectToLogin();
    exit;
}

$transactionId = $_GET['id'] ?? null;
if (!$transactionId) {
    displayError("No transaction specified.");
    exit;
}

$db = getDB();

// Load transaction + service info
$stmt = $db->prepare(
    "SELECT
      t.*,
      s.title         AS service_title,
      s.price         AS service_price,
      u.username      AS freelancer_name
    FROM Transactions t
    JOIN Services s   ON t.service_id  = s.id
    JOIN Users u      ON s.freelancer_id = u.id
    WHERE t.id = ? AND t.client_id = ?"
);
$stmt->execute([$transactionId, $currentUserId]);
$tx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tx) {
    displayError("Transaction not found.");
    exit;
}

include_once '../../templates/header.php';
?>

<div style="max-width:600px;margin:2rem auto;padding:1.5rem;background:#fff;border:1px solid #ddd;border-radius:8px;">
    <h2>Transaction Pending</h2>
    <p>Your payment for <strong><?= htmlspecialchars($tx['service_title']) ?></strong> is being processed.</p>
    <ul>
        <li><strong>Amount:</strong> $<?= htmlspecialchars($tx['service_price']) ?></li>
        <li><strong>Seller:</strong> <?= htmlspecialchars($tx['freelancer_name']) ?></li>
        <li><strong>Transaction ID:</strong> <?= htmlspecialchars($tx['id']) ?></li>
        <li><strong>Status:</strong> <?= htmlspecialchars(ucfirst($tx['status'])) ?></li>
        <li><strong>Requested at:</strong> <?= date("F j, Y, g:i a", strtotime($tx['created_at'])) ?></li>
    </ul>
    <p>We’ll notify you once the payment is complete.</p>
    <a href="view.php?id=<?= htmlspecialchars($tx['service_id']) ?>" class="btn">Back to Service</a>
</div>

<?php include_once '../../templates/footer.php'; ?>