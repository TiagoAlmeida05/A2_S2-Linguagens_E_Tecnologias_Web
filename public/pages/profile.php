<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$currentUserId = getCurrentUserId();
$user = [];
if ($currentUserId) {
    $user = getUserById($currentUserId);
    if (!$user) {
        displayError('User not found');
        redirect('/');
    }
}


// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        displayError('All password fields are required.');
    } elseif ($newPassword !== $confirmPassword) {
        displayError('New passwords do not match.');
    } elseif (strlen($newPassword) < 6) {
        displayError('Password must be at least 6 characters.');
    } else {
        if (updateUserPassword($currentUserId, $currentPassword, $newPassword)) {
            displaySuccess('Password updated successfully!');
        } else {
            displayError('Current password is incorrect.');
        }
    }
    
    redirect('/pages/profile.php');
}

// Include header
include_once '../templates/header.php';
?>

<div class="profile-container">
    <h1 class="gradient-heading">Your Profile</h1>
    
    <div class="profile-section">
        <h2>Account Information</h2>
        <div class="user-info">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>

    <div class="password-section">
        <h2>Change Password</h2>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" name="change_password" class="btn btn-block">Change Password</button>
        </form>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>