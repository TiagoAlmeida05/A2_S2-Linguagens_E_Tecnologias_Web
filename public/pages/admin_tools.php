<?php
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

// Redirect if not logged in or not admin
if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$currentUserId = getCurrentUserId();
$user = getUserById($currentUserId);

if (!$user || !$user['is_admin']) {
    displayError("Access denied. Admins only.");
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    // Add Category
    if (isset($_POST['add_category'])) {
        $categoryName = sanitizeInput($_POST['category_name']);
        $categoryDesc = sanitizeInput($_POST['category_description']);

        if (!$categoryName) {
            displayError('Category name is required.');
        } else {
            $stmt = $db->prepare("INSERT INTO Categories (name, description) VALUES (?, ?)");
            $stmt->execute([$categoryName, $categoryDesc]);
            displaySuccess('Category added!');
        }
    }

    // Promote User
    if (isset($_POST['promote_user'])) {
        $usernameToPromote = sanitizeInput($_POST['username']);
        $stmt = $db->prepare("UPDATE Users SET is_admin = 1 WHERE username = ?");
        $stmt->execute([$usernameToPromote]);
        displaySuccess('User promoted to admin!');
    }
}

// Include header
include_once '../templates/header.php';
?>

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
                <input type="text" name="category_description" class="form-control">
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

<?php include_once '../templates/footer.php'; ?>
