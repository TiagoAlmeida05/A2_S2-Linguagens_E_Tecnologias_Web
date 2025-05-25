<?php
require_once '../../includes/config.php';
require_once '../../includes/utils.php';

// Check if user is logged in
if (!isLoggedIn()) {
    displayError('You must be logged in to complete transactions.');
    redirect('/pages/login.php');
}

// Check if transaction_id and CSRF token are provided
if (!isset($_POST['transaction_id']) || !validateCSRFToken($_POST['csrf_token'] ?? '')) {
    displayError('Invalid request.');
    redirect('/pages/services/list.php?mine=1');
}

$transactionId = $_POST['transaction_id'];
$userId = getCurrentUserID();

try {
    $db = getDB();
    
    // Verify that the transaction belongs to one of the user's services
    $stmt = $db->prepare("
        SELECT t.* 
        FROM Transactions t
        JOIN Services s ON t.service_id = s.id
        WHERE t.id = ? AND s.freelancer_id = ? AND t.status = 'pending'
    ");
    $stmt->execute([$transactionId, $userId]);
    
    if (!$stmt->fetch()) {
        displayError('Invalid transaction or unauthorized access.');
        redirect('/pages/services/list.php?mine=1');
    }
    
    // Update transaction status to completed
    $stmt = $db->prepare("UPDATE Transactions SET status = 'completed' WHERE id = ?");
    $stmt->execute([$transactionId]);
    
    displaySuccess('Transaction marked as completed successfully.');
} catch (PDOException $e) {
    displayError('An error occurred while completing the transaction.');
}

redirect('/pages/services/list.php?mine=1');
?> 