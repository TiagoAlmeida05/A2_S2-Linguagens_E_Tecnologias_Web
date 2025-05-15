<?php
require_once 'config.php';

// General function to be used for redirects
function redirect($path) {
    header('Location: ' . SITE_URL . $path);
    exit;
}

// Sanitize input -> remove whitespace, slashes, and convert special characters for security
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

// Display error message
function displayError($message) {
    $_SESSION['error'] = $message;
}

// Display success message
function displaySuccess($message) {
    $_SESSION['success'] = $message;
}

 // Get the popping messages and eventually clear them
function getMessages() {
    $messages = [
        'error' => $_SESSION['error'] ?? null,
        'success' => $_SESSION['success'] ?? null
    ];
    
    unset($_SESSION['error']);
    unset($_SESSION['success']);
    
    return $messages;
}
?> 