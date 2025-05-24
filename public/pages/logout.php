<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';

// Clear all session variables
$_SESSION = [];

// If a session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session and show success alert
session_start();
session_unset();
session_destroy();

// Redirect to login page
displaySuccess('You have been logged out successfully.');
redirect('/pages/login.php');
?> 