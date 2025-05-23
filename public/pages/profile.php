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

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Handle password change
    if (isset($_POST['change_password'])) {
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

    //Handle account info update
    if(isset($_POST['update_info'])){
        $newName = sanitizeInput($_POST['name']);
        $newUsername = sanitizeInput($_POST['username']);
        $newEmail = sanitizeInput($_POST['email']);

        if (!$newName || !$newUsername || !$newEmail) {
            displayError('Name, Username, and Email cannot be empty.');
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            displayError('Invalid email format.');
        } else {
            $db = getDB();
            $stmt = $db->prepare("UPDATE Users SET name = ?, username = ?, email = ? WHERE id = ?");
            $stmt->execute([$newName, $newUsername, $newEmail, $currentUserId]);

            displaySuccess('Account info updated!');
            redirect('/pages/profile.php');
        }
    }
    // Handle admin actions: Add Category
    if (isset($_POST['add_category']) && $user['is_admin']) {
        $categoryName = sanitizeInput($_POST['category_name']);
        $categoryDesc = sanitizeInput($_POST['category_description']);

        if (!$categoryName) {
            displayError('Category name is required.');
        } else {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO Categories (name, description) VALUES (?, ?)");
            $stmt->execute([$categoryName, $categoryDesc]);

            displaySuccess('Category added!');
            redirect('/pages/profile.php');
        }
    }
    // Handle admin actions: Promote user to admin
    if (isset($_POST['promote_user']) && $user['is_admin']) {
        $usernameToPromote = $_POST['username'];

        $db = getDB();
        $stmt = $db->prepare("UPDATE Users SET is_admin = 1 WHERE username = ?");
        $stmt->execute([$usernameToPromote]);

        displaySuccess('User promoted to admin!');
        redirect('/pages/profile.php');
    }
}


// Include header
include_once '../templates/header.php';
?>

<div class="profile-container">
    <h1 class="gradient-heading">Your Profile</h1>
    
    <div class="profile-section">
        <h2>Account Information</h2>
        <div class="user-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
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

    <div class="password-section">
        <h2>Update Account Info</h2>

        <form action="" method="post">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="username">New Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">New Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
            </div>

            <button type="submit" name="update_info" class="btn btn-block">Update Info</button>
        </form>
    </div>
</div>
<?php if ($user['is_admin']): ?>

    <div class="profile-container">
        <h1 class="gradient-heading">Admin Tools</h1>

        <div class="profile-section">
            <h2>Create New Category</h2>

            <form action="" method="post">
                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" name="category_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="category_description">Description</label>
                    <input type="text" name="category_description" class="form-control" required>
                </div>

                <button type="submit" name="add_category" class="btn btn-block">Add Category</button>
            </form>
        </div>

        <div class="profile-section">
            <h2>Promote User to Admin</h2>

            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <button type="submit" name="promote_user" class="btn btn-block">Promote</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include_once '../templates/footer.php'; ?>