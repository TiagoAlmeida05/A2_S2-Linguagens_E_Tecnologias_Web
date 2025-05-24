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

$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    displayError("No service specified.");
    exit;
}

$db = getDB();

// Load service and ensure user is not the freelancer
$stmt = $db->prepare("SELECT * FROM Services WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$service) {
    displayError("Service not found.");
    exit;
}
if ($service['freelancer_id'] == $currentUserId) {
    displayError("You cannot pay for your own service.");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new transaction (status defaults to 'pending')
    $stmt = $db->prepare(
        "INSERT INTO Transactions (client_id, service_id) VALUES (:client, :service)"
    );
    $stmt->execute([
        ':client'  => $currentUserId,
        ':service' => $serviceId
    ]);
    $transactionId = $db->lastInsertId();

    // Redirect to status page
    header("Location: transaction_status.php?id={$transactionId}");
    exit;
}

include_once '../../templates/header.php';
?>

<style>
.payment-container {
    max-width: 500px;
    margin: 2rem auto;
    padding: 1.5rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
}
.payment-container h2 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
}
.payment-container p {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}
.payment-container .form-group {
    margin-bottom: 1rem;
}
.payment-container label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}
.payment-container input {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
}
.payment-container .btn {
    font-size: 1rem;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    margin-top: 0.5rem;
}
.payment-container .btn-success {
    background-color: #28a745;
    color: #fff;
    border: none;
    cursor: pointer;
}
.payment-container .btn-success:hover {
    background-color: #218838;
}
.payment-container .btn-cancel {
    background-color: #fff;
    color: #333;
    border: 1px solid #333;
    margin-left: 1rem;
}
.payment-container .btn-cancel:hover {
    background-color: #f8f9fa;
    color: #FFF;
}
</style>

<div class="payment-container">
    <h2>Pay for Service: <?= htmlspecialchars($service['title']) ?></h2>
    <p>Price: <strong>$<?= htmlspecialchars($service['price']) ?></strong></p>

    <form method="POST">
        <div class="form-group">
            <label for="card_number">Card Number</label>
            <input
                type="text"
                name="card_number"
                id="card_number"
                required
                maxlength="19"
                placeholder="1234 5678 9012 3456"
            >
        </div>

        <div class="form-group">
            <label for="expiration_date">Expiration Date (MM/YY)</label>
            <input
                type="text"
                name="expiration_date"
                id="expiration_date"
                required
                placeholder="MM/YY"
            >
        </div>

        <div class="form-group">
            <label for="cvc">CVC</label>
            <input
                type="text"
                name="cvc"
                id="cvc"
                required
                maxlength="4"
                placeholder="123"
            >
        </div>

        <button type="submit" class="btn btn-success">Confirm Payment</button>
        <a href="view.php?id=<?= $serviceId ?>" class="btn btn-cancel">Cancel</a>
    </form>
</div>

<?php include_once '../../templates/footer.php'; ?>