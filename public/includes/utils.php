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

/**
 * Generate a CSRF token and store it in the session
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate and sanitize uploaded file
 */
function validateUploadedFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('File size exceeds limit.');
        case UPLOAD_ERR_PARTIAL:
            throw new RuntimeException('File was only partially uploaded.');
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file was uploaded.');
        case UPLOAD_ERR_NO_TMP_DIR:
            throw new RuntimeException('Missing a temporary folder.');
        case UPLOAD_ERR_CANT_WRITE:
            throw new RuntimeException('Failed to write file to disk.');
        case UPLOAD_ERR_EXTENSION:
            throw new RuntimeException('A PHP extension stopped the file upload.');
        default:
            throw new RuntimeException('Unknown upload error.');
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('File size exceeds limit.');
    }

    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $fileType = $finfo->file($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        throw new RuntimeException('Invalid file type.');
    }

    return true;
}

/**
 * Generate a safe filename for upload
 */
function generateSafeFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    return $safeName;
}
?> 