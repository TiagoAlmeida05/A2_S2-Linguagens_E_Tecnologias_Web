<?php 
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

if (!isLoggedIn()) {
    header("Location: /pages/login.php");
    exit;
}

$db = getDB();

$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    displayError("No service selected.");
    exit;
}

// Fetch job
$stmt = $db->prepare("
    SELECT * FROM Services WHERE id = ?
");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    displayError("Service not found.");
    exit;
}

// Permission check
if (getCurrentUserId() != $service['freelancer_id'] && !isAdmin()) {
    displayError("You don’t have permission to edit this service.");
    exit;
}

// Fetch categories
$categories = $db->query("SELECT id, name FROM Categories")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_job'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $delivery_time = $_POST['delivery_time'] ?? '';

    if (empty($title) || empty($description) || empty($category_id) || empty($price) || empty($delivery_time)) {
        displayError('All fields are required.');
    } else {
        try {
            $stmt = $db->prepare("
                UPDATE Services SET title = ?, description = ?, category_id = ?, price = ?, delivery_time = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $category_id, $price, $delivery_time, $serviceId]);

            displaySuccess('Service updated successfully!');
            header("Location: /pages/services/view.php?id=" . $serviceId);
            exit;
        } catch (PDOException $e) {
            displayError('Failed to update service: ' . $e->getMessage());
        }
    }
}

include_once '../../templates/header.php';
?>

<div class="profile-container">
    <h1 class="gradient-heading">Edit Service</h1>
    <form action="" method="POST">
        <div class="form-group">
            <label>Service Title:</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($service['title']); ?>" required>
        </div>

        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" class="form-control" required><?php echo htmlspecialchars($service['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Category:</label>
            <select name="category_id" required>
                <?php foreach($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php if ($category['id'] == $service['category_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-group">
            <label>Price ($):</label>
            <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($service['price']); ?>" required>
        </div>

        <div class="form-group">
            <label>Delivery Time (days):</label>
            <input type="number" name="delivery_time" class="form-control" value="<?php echo htmlspecialchars($service['delivery_time']); ?>" required>
        </div>

        <button type="submit" name="edit_job" class="btn btn-block">Save Changes</button>
    </form>
</div>

<?php include_once '../../templates/footer.php'; ?>
