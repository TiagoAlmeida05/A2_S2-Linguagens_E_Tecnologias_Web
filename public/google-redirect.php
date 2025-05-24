<?php
require_once 'includes/config.php';
require_once 'includes/utils.php';
require_once 'includes/user.php';
require __DIR__ . "/vendor/autoload.php";

// Initialize Google Client
$client = new Google\Client;
$client->setClientId('345371216571-p2r3fof3jb5r4m0s3vm3me6sklru2mje.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:9000/google-redirect.php');

// Get token from code
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token["access_token"]);

    $oauth = new Google\Service\Oauth2($client);
    $userinfo = $oauth->userinfo->get();
    $email = $userinfo->email;
    $name = $userinfo->name;

    // Check if user already exists
    $user = getUserByEmail($email);
    
    if ($user) {
        // User exists, log them in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        displaySuccess('Login successful!');
        redirect('/');
    } else {
        // Create new user
        // Generate username from email (remove domain and special characters)
        $username = preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]);
        
        // Generate random password (they'll use Google to login)
        $password = bin2hex(random_bytes(8));
        
        // Create user in database
        $userId = registerUser($username, $email, $password, $name);
        
        if ($userId) {
            // Log in the new user
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = 0;
            
            displaySuccess('Account created and logged in successfully!');
            redirect('/');
        } else {
            displayError('Failed to create account. Please try again.');
            redirect('/pages/register.php');
        }
    }
} else {
    displayError('No authorization code received from Google.');
    redirect('/pages/login.php');
}
