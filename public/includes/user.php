<?php
require_once 'config.php';
require_once 'utils.php';

// Function to register a new user into the database
function registerUser($username, $email, $password, $name) {
    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($name)) {
        return false;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    
    try {
        $db = getDB();
        
        // Check if username or email already exists
        $stmt = $db->prepare("SELECT id FROM Users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            return false; // Username or email already exists
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $db->prepare("INSERT INTO Users (username, email, password, name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $name]);
        
        return $db->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

// Function to login a user that is in the database
function loginUser($username, $password) {
    if (empty($username) || empty($password)) {
        return false;
    }
    
    try {
        $db = getDB();
        
        // Check if input is email or username
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $stmt = $db->prepare("SELECT id, username, email, password, is_admin FROM Users WHERE $field = ?");
        $stmt->execute([$username]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from user data
            unset($user['password']);
            return $user;
        }
        
        return false;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to get user details using his ID
function getUserById($userId) {
    if (empty($userId)) {
        return false;
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT id, username, email, name, is_admin, created_at FROM Users WHERE id = ?");
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to update user password details
function updateUserPassword($userId, $currentPassword, $newPassword) {
    if (empty($userId) || empty($currentPassword) || empty($newPassword)) {
        return false;
    }
    
    try {
        $db = getDB();
        
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM Users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE Users SET password = ? WHERE id = ?");
        
        return $stmt->execute([$hashedPassword, $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

function getUserByEmail($email) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}
?> 