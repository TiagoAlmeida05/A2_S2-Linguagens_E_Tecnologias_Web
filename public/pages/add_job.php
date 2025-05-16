<?php 
require_once '../includes/config.php';
require_once '../includes/utils.php';
require_once '../includes/user.php';

$db = getDB();

try {
    $stmt = $db->query("SELECT id, name FROM Categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    displayError('Failed to load categories: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_job'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $delivery_time = $_POST['delivery_time'] ?? '';
    $freelancer_id = getCurrentUserId(); // Assuming you have this function to get current logged-in user
  
    // Basic validation
    if (empty($title) || empty($description) || empty($category_id) || empty($price) || empty($delivery_time)) {
        displayError('All fields are required.');
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO Services (freelancer_id, title, description, category_id, price, delivery_time)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$freelancer_id, $title, $description, $category_id, $price, $delivery_time]);

            displaySuccess('Service added successfully!');
            header("Location: /pages/homepage.php");
            exit;
        } catch (PDOException $e) {
            displayError('Failed to add service: ' . $e->getMessage());
        }
    }
}

include_once '../templates/header.php'; ?>

<div class="profile-container">
    <h1 class="gradient-heading"> Add a New Service</h1>
    <form action="" method="POST">
        <div class="form-group">
            <label for="service_title">Service Title:</label>
            <input type="text" id="service_title" name="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label>Category:</label>
            <select name="category_id" required>
                <?php foreach($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-group">
            <label for="price">Price ($):</label>
            <input type="number" id="price" name="price" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="delivery_time">Delivery Time (days):</label>
            <input type="number" id="delivery_time" name="delivery_time" class="form-control" required>
        </div>

        <button type="submit" name="add_job" class="btn btn-block">Add Service</button>
    </form>
</div>

<?php include_once '../templates/footer.php'; ?>