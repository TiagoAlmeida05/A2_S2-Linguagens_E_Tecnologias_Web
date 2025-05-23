<?php
require_once '../includes/config.php';
require_once '../includes/user.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = getDB();
$currentUserId = getCurrentUserId();

$transactionId = isset($_GET['transaction_id']) ? (int)$_GET['transaction_id'] : 0;
if (!$transactionId) {
    die('Invalid transaction.');
}

$stmt = $db->prepare("
    SELECT t.*, s.title, s.price, u.username AS freelancer_name
    FROM Transactions t
    JOIN Services s ON t.service_id = s.id
    JOIN Users u ON s.freelancer_id = u.id
    WHERE t.id = ? AND t.client_id = ?
");

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use POST transaction_id if available (extra safety)
    $transactionId = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : $transactionId;
    
    $action = $_POST['action'] ?? '';

    if ($action === 'complete') {
        $stmtUpdate = $db->prepare("UPDATE Transactions SET status = 'completed' WHERE id = ?");
        $stmtUpdate->execute([$transactionId]);
        $message = "Payment completed successfully! Your order is confirmed.";
        $messageType = 'success';
    } elseif ($action === 'cancel') {
        $stmtUpdate = $db->prepare("UPDATE Transactions SET status = 'cancelled' WHERE id = ?");
        $stmtUpdate->execute([$transactionId]);
        $message = "Payment cancelled. Your order has been cancelled.";
        $messageType = 'error';
    } else {
        $message = "Unknown action.";
        $messageType = 'error';
    }
}

// Always fetch fresh transaction info after any POST update or on first load
$stmt->execute([$transactionId, $currentUserId]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    die('Transaction not found or unauthorized.');
}

include_once '../templates/header.php';
?>

<div class="order-container">
    <h2 class="order-heading">Confirm Your Order</h2>
    <p class="order-text">You are ordering the service: <strong class="order-strong"><?php echo htmlspecialchars($transaction['title']); ?></strong></p>
    <p class="order-text">Freelancer: <strong class="order-strong"><?php echo htmlspecialchars($transaction['freelancer_name']); ?></strong></p>
    <p class="order-text">Price: <strong class="order-strong">$<?php echo htmlspecialchars($transaction['price']); ?></strong></p>

    <?php if (!empty($message)): ?>
        <div class="<?php echo $messageType === 'error' ? 'message-error' : 'message-success'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($transaction['status'] === 'pending'): ?>
    <form action="payment.php?transaction_id=<?php echo $transactionId; ?>" method="POST">
        <input type="hidden" name="transaction_id" value="<?php echo $transactionId; ?>">
        <button type="submit" name="action" value="complete" class="order-btn">Complete Payment</button>
        <button type="submit" name="action" value="cancel" class="order-btn cancel">Cancel</button>
    </form>
    <?php else: ?>
        <p>Status: <strong><?php echo htmlspecialchars(ucfirst($transaction['status'])); ?></strong></p>
        <a href="<?php echo SITE_URL; ?>/pages/services/list.php" class="back-link">Back to Services</a>
    <?php endif; ?>
</div>

<?php include_once '../templates/footer.php'; ?>
