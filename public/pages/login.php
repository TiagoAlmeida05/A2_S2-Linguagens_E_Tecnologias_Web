<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';
require __DIR__ . "/../vendor/autoload.php";

// Initialize Google Client
$client = new Google\Client;
$client->setClientId('345371216571-p2r3fof3jb5r4m0s3vm3me6sklru2mje.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:9000/google-redirect.php');
$client->addScope('email');
$client->addScope('profile');
$googleAuthUrl = $client->createAuthUrl();

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/');
}

// Initialize username variable
$username = '';

// Get username from session if available (after failed validation)
if (isset($_SESSION['login_username'])) {
    $username = $_SESSION['login_username'];
    
    // Clear the session data
    unset($_SESSION['login_username']);
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $formValid = true;
    
    if (empty($username) || empty($password)) {
        displayError('Please enter both username and password.');
        $formValid = false;
    } else {
        $user = loginUser($username, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            displaySuccess('Login successful!');
            redirect('/');
            exit;
        } else {
            displayError('Invalid username or password.');
            $formValid = false;
        }
    }
    
    // If login failed, store username in session and redirect
    if (!$formValid) {
        $_SESSION['login_username'] = $username;
        
        // Redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Include header
include_once '../templates/header.php';
?>

<div class="form-container">
    <h2>Login</h2>
    
    <form action="" method="post">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-block">Login</button>
        </div>
    </form>

    <div class="social-login">
        <p>Or continue with</p>
        <a href="<?php echo $googleAuthUrl; ?>" class="google-btn">
            <img src="<?php echo SITE_URL; ?>/assets/images/google-icon.svg" alt="Google Icon">
            Sign in with Google
        </a>
    </div>
    
    <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/pages/register.php">Register</a></p>
</div>

<?php include_once '../templates/footer.php'; ?> 