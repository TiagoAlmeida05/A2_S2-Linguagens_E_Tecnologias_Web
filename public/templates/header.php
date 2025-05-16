<?php 
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
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
                    <?php if(isLoggedIn()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/pages/add_job.php" class="auth-link">Add Service</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/services/list.php">Your Services</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/profile.php">Profile</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/logout.php">Logout</a></li>
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