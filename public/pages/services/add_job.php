<?php 
require_once '../../includes/config.php';
require_once '../../includes/utils.php';
require_once '../../includes/user.php';

// Check if user is logged in
if (!isLoggedIn()) {
    displayError('You must be logged in to add a service.');
    redirect('/pages/login.php');
}

$db = getDB();
$userId = getCurrentUserID();

try {
    $stmt = $db->query("SELECT id, name FROM Categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    displayError('Failed to load categories: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        displayError('Invalid request.');
        redirect('/pages/services/add_job.php');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $categoryId = intval($_POST['category_id'] ?? 0);
    $deliveryTime = intval($_POST['delivery_time'] ?? 0);

    // Validate input
    if (empty($title) || empty($description) || $price <= 0 || $categoryId <= 0 || $deliveryTime <= 0) {
        displayError('All fields are required and must be valid.');
        redirect('/pages/services/add_job.php');
    }

    try {
        $db->beginTransaction();

        // Insert service
        $stmt = $db->prepare("
            INSERT INTO Services (title, description, price, category_id, freelancer_id, delivery_time)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $price, $categoryId, $userId, $deliveryTime]);
        $serviceId = $db->lastInsertId();

        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    try {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $tmpName,
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        validateUploadedFile($file);
                        
                        $safeFilename = generateSafeFilename($file['name']);
                        $uploadPath = $uploadDir . $safeFilename;

                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $stmt = $db->prepare("INSERT INTO ServiceMedia (service_id, file_path, type) VALUES (?, ?, 'image')");
                            $stmt->execute([$serviceId, $safeFilename]);
                        }
                    } catch (RuntimeException $e) {
                        displayError('Image upload failed: ' . $e->getMessage());
                        $db->rollBack();
                        redirect('/pages/services/add_job.php');
                    }
                }
            }
        }

        // Handle video uploads
        if (!empty($_FILES['videos']['name'][0])) {
            foreach ($_FILES['videos']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['videos']['error'][$key] === UPLOAD_ERR_OK) {
                    try {
                        $file = [
                            'name' => $_FILES['videos']['name'][$key],
                            'type' => $_FILES['videos']['type'][$key],
                            'tmp_name' => $tmpName,
                            'error' => $_FILES['videos']['error'][$key],
                            'size' => $_FILES['videos']['size'][$key]
                        ];
                        
                        validateUploadedFile($file, ['video/mp4', 'video/webm', 'video/ogg'], 10485760); // 10MB limit for videos
                        
                        $safeFilename = generateSafeFilename($file['name']);
                        $uploadPath = $uploadDir . $safeFilename;

                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $stmt = $db->prepare("INSERT INTO ServiceMedia (service_id, file_path, type) VALUES (?, ?, 'video')");
                            $stmt->execute([$serviceId, $safeFilename]);
                        }
                    } catch (RuntimeException $e) {
                        displayError('Video upload failed: ' . $e->getMessage());
                        $db->rollBack();
                        redirect('/pages/services/add_job.php');
                    }
                }
            }
        }

        $db->commit();
        displaySuccess('Service added successfully!');
        redirect('/pages/services/list.php?mine=1');
    } catch (PDOException $e) {
        $db->rollBack();
        displayError('An error occurred while adding the service: ' . $e->getMessage());
        redirect('/pages/services/add_job.php');
    }
}

include_once '../../templates/header.php'; ?>

<div class="profile-container">
    <h1 class="gradient-heading">Add a New Service</h1>
    <form action="<?php echo SITE_URL; ?>/pages/services/add_job.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="delivery_time">Delivery Time (days)</label>
            <input type="number" id="delivery_time" name="delivery_time" class="form-control" min="1" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-control" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="images">Service Images</label>
            <input type="file" id="images" name="images[]" class="form-control" accept="image/jpeg,image/png,image/gif" multiple>
            <small class="form-text text-muted">Maximum file size: 5MB per image. Allowed formats: JPEG, PNG, GIF</small>
            <div id="imagePreview" class="preview-container"></div>
        </div>

        <div class="form-group">
            <label for="videos">Service Videos</label>
            <input type="file" id="videos" name="videos[]" class="form-control" accept="video/mp4,video/webm,video/ogg" multiple>
            <small class="form-text text-muted">Maximum file size: 10MB per video. Allowed formats: MP4, WebM, OGG</small>
            <div id="videoPreview" class="preview-container"></div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Add Service</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        [...e.target.files].forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.margin = '5px';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });
    });

    // Video preview
    document.getElementById('videos').addEventListener('change', function(e) {
        const preview = document.getElementById('videoPreview');
        preview.innerHTML = '';
        
        [...e.target.files].forEach(file => {
            if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.controls = true;
                video.style.maxWidth = '200px';
                video.style.margin = '5px';
                preview.appendChild(video);
            }
        });
    });
});
</script>

<?php include_once '../../templates/footer.php'; ?>
