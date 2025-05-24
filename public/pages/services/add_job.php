<?php 
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';


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
    $freelancer_id = getCurrentUserId();

    if (empty($title) || empty($description) || empty($category_id) || empty($price) || empty($delivery_time)) {
        displayError('All fields are required.');
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO Services (freelancer_id, title, description, category_id, price, delivery_time)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$freelancer_id, $title, $description, $category_id, $price, $delivery_time]);
            $serviceId = $db->lastInsertId();

            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $fileName = basename($_FILES['images']['name'][$key]);
                    $targetPath = "../uploads/$fileName";

                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $stmt = $db->prepare("INSERT INTO ServiceMedia (service_id, file_path, type) VALUES (?, ?, 'image')");
                        if (!$stmt->execute([$serviceId, $fileName])) {
                            displayError("Failed to save image info to database for file: $fileName");
                        }
                    } else {
                        displayError("Failed to move uploaded image file: $fileName");
                    }
                }
            } else {
                echo "No images uploaded.<br>";
            }

            if (!empty($_FILES['videos']['name'][0])) {
                foreach ($_FILES['videos']['tmp_name'] as $key => $tmpName) {
                    $fileName = basename($_FILES['videos']['name'][$key]);
                    $targetPath = "../uploads/$fileName";

                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $stmt = $db->prepare("INSERT INTO ServiceMedia (service_id, file_path, type) VALUES (?, ?, 'video')");
                        if (!$stmt->execute([$serviceId, $fileName])) {
                            displayError("Failed to save video info to database for file: $fileName");
                        }
                    } else {
                        displayError("Failed to move uploaded video file: $fileName");
                    }
                }
            } else {
                echo "No videos uploaded.<br>";
            }

            displaySuccess('Service added successfully!');
            header("Location: /pages/home.php");
            exit;
        } catch (PDOException $e) {
            displayError('Failed to add service: ' . $e->getMessage());
        }
    }
}

include_once '../../templates/header.php'; ?>

<div class="profile-container">
    <h1 class="gradient-heading"> Add a New Service</h1>
    <form action="" method="POST" enctype="multipart/form-data">
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

        <div class="form-group">
            <label>Upload Images:</label>
            <input type="file" id="imageInput" name="images[]" accept="image/*" multiple>
            <div id="imagePreview"></div>
        </div>

        <div class="form-group">
            <label>Upload Videos:</label>
            <input type="file" id="videoInput" name="videos[]" accept="video/*" multiple>
            <div id="videoPreview"></div>
        </div>

        <button type="submit" name="add_job" class="btn btn-block">Add Service</button>
    </form>
</div>

<script src="<?php echo SITE_URL; ?>/assets/jvs/js.js"></script>
<?php include_once '../../templates/footer.php'; ?>
