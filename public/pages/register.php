<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

if (isLoggedIn()) {
    redirect('/');
}

// Default form values
$username = '';
$email = '';
$name = '';

// Restore old form data if validation failed, this ensures the user does not have to repeat everything from scratch
if (!empty($_SESSION['form_data'])) {
    $username = $_SESSION['form_data']['username'] ?? '';
    $email = $_SESSION['form_data']['email'] ?? '';
    $name = $_SESSION['form_data']['name'] ?? '';
    unset($_SESSION['form_data']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $name = sanitizeInput($_POST['name'] ?? '');

    $valid = true;

    // Check if inputs are valid

    // If everything is filled:
    if (!$username || !$email || !$password || !$name) {
        displayError('All fields are required.');
        $valid = false;
    // If the 2 passwords match
    } elseif ($password !== $confirm) {
        displayError('Passwords do not match.');
        $valid = false;
    // If the password is at least 6 characters long
    } elseif (strlen($password) < 6) {
        displayError('Password must be at least 6 characters.');
        $valid = false;
    // If the email input has a valid format
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        displayError('Invalid email format.');
        $valid = false;
    // If all passes we register the user!
    } else {
        $userId = registerUser($username, $email, $password, $name);

        if ($userId) {
            displaySuccess('Registration complete! Please login.');
            redirect('/pages/login.php');
            exit;
        // Quick check if the username/email already exists
        } else {
            displayError('Username or email already exists.');
            $valid = false;
        }
    }

    // If validation failed, store the form and redirect
    if (!$valid) {
        $_SESSION['form_data'] = [
            'username' => $username,
            'email' => $email,
            'name' => $name,
        ];
        header('Location: /pages/register.php');
        exit;
    }
}

include '../templates/header.php';
?>

<div class="form-container">
    <h2>Register</h2>
    
    <form action="" method="post">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-block">Register</button>
        </div>
    </form>
    
    <p>Already have an account? <a href="<?php echo SITE_URL; ?>/pages/login.php">Login</a></p>
</div>

<?php include_once '../templates/footer.php'; ?> 