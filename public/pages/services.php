<?php
require_once '../includes/config.php';
require_once '../includes/user.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = getDB();
$currentUserId = getCurrentUserId();

// Orders YOU placed (client_id = you)
$stmtOrdersPlaced = $db->prepare("
    SELECT t.id AS transaction_id, s.title, s.price, u.username AS freelancer_name, t.status, t.created_at
    FROM Transactions t
    JOIN Services s ON t.service_id = s.id
    JOIN Users u ON s.freelancer_id = u.id
    WHERE t.client_id = ?
    ORDER BY t.created_at DESC
");
$stmtOrdersPlaced->execute([$currentUserId]);
$ordersPlaced = $stmtOrdersPlaced->fetchAll(PDO::FETCH_ASSOC);

// Orders placed ON YOU (freelancer_id = you)
$stmtOrdersReceived = $db->prepare("
    SELECT t.id AS transaction_id, s.title, s.price, u.username AS client_name, t.status, t.created_at
    FROM Transactions t
    JOIN Services s ON t.service_id = s.id
    JOIN Users u ON t.client_id = u.id
    WHERE s.freelancer_id = ?
    ORDER BY t.created_at DESC
");
$stmtOrdersReceived->execute([$currentUserId]);
$ordersReceived = $stmtOrdersReceived->fetchAll(PDO::FETCH_ASSOC);

include_once '../templates/header.php';
?>

<div class="container orders-page">
    <h2>Your Orders & Sales</h2>
    <div class="toggle-buttons">
        <button id="btn-orders-placed" class="toggle-btn active">Orders You Placed</button>
        <button id="btn-orders-received" class="toggle-btn">Orders Placed On You</button>
    </div>

    <div id="orders-placed-list" class="orders-list">
        <?php if (empty($ordersPlaced)): ?>
            <p>No orders placed yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Freelancer</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Ordered At</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ordersPlaced as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo htmlspecialchars($order['freelancer_name']); ?></td>
                        <td>$<?php echo htmlspecialchars($order['price']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($order['status'])); ?></td>
                        <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        <td><a href="payment.php?transaction_id=<?php echo $order['transaction_id']; ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="orders-received-list" class="orders-list" style="display:none;">
        <?php if (empty($ordersReceived)): ?>
            <p>No orders placed on you yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Client</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Ordered At</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ordersReceived as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                        <td>$<?php echo htmlspecialchars($order['price']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($order['status'])); ?></td>
                        <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        <td><a href="payment.php?transaction_id=<?php echo $order['transaction_id']; ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/services.js" defer></script>

<?php include_once '../templates/footer.php'; ?>
