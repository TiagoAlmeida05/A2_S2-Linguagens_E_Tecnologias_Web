<?php  
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
$currentUser = null;
$currentUserId = getCurrentUserId();
if ($currentUserId) {
    $currentUser = getUserById($currentUserId);

    // Fetch unread message count
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) AS unread_count
        FROM Messages
        WHERE receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$currentUserId]);
    $unreadResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUnread = $unreadResult['unread_count'] ?? 0;
} else {
    $totalUnread = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance Marketplace</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="<?php echo SITE_URL; ?>/" class="site-title">
                <img src="<?php echo SITE_URL; ?>/assets/svgs/logo.svg" alt="Freelance Marketplace Logo" class="logo">
            </a>
            <nav>
                <ul>
                    <?php if (isLoggedIn()): ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/pages/messages.php" title="Inbox">
                                <i class="fa fa-envelope"></i>
                                <?php
                                    // Optional: If you want to show unread count badge here, add the logic to get totalUnread before including this header
                                    if (isset($totalUnread) && $totalUnread > 0): ?>
                                        <span class="badge"><?php echo $totalUnread; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/services/list.php">All Services</a></li>
                    <?php if(isLoggedIn()): ?>
                        <li><button onclick="window.location.href='<?php echo SITE_URL; ?>/pages/add_job.php'" class="add-service">Add Service</button></li>
                        <div class="dropdown">
                            <button class="dropdown-toggle" type="button">
                                ☰ Options
                            </button>
                            <div class="dropdown-menu">
                                <a href="<?php echo SITE_URL; ?>/pages/profile.php">Profile</a>
                                <?php if ($currentUser['is_admin']): ?>
                                  <a href="<?php echo SITE_URL; ?>/pages/admin_tools.php">Admin Tools</a>
                                <?php endif; ?>
                                <a href="<?php echo SITE_URL; ?>/pages/services/list.php?mine=1">My Services</a>
                                <a href="<?php echo SITE_URL; ?>/pages/services/paid_services.php">Paid Services</a>


                                <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="logout-link">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/pages/login.php" class="auth-link">Login</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/register.php" class="auth-link">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <?php
    // Display flash messages
    $messages = getMessages();
    if ($messages['error'] || $messages['success']): ?>
    <div class="message-container">
        <div class="container">
            <?php if ($messages['error']): ?>
                <div class="alert alert-error" id="error-alert"><?php echo $messages['error']; ?></div>
            <?php endif; ?>
            
            <?php if ($messages['success']): ?>
                <div class="alert alert-success" id="success-alert"><?php echo $messages['success']; ?></div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Auto-dismiss alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length) {
                alerts.forEach(function(alert) {
                    setTimeout(function() {
                        if (alert.parentNode) {
                            alert.style.opacity = '0';
                            setTimeout(function() {
                                alert.parentNode.removeChild(alert);
                            }, 500);
                        }
                    }, 3000); // 3 seconds
                });
            }
        });
    </script>
    <?php endif; ?>
    
    <main>
        <div class="container">
