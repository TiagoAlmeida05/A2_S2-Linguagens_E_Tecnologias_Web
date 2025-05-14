<?php
include 'general.php';
session_start();
try{
    $db = new PDO('sqlite:workhive.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $user_id = $_POST['user_id'];       
        $description = $_POST['description'];

        $stmt = db->prepare("INSERT INTO jobs(name, description, category_id, price, user_id VALUES(?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $category_id, $price, $user_id]);

        echo "Service added successfully!";       
    }

    $stmr = $db->query("SELECT id, name FROM categorias");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
}catch(PDOException $e){
    echo "Database error: " . $e->getMessage();
}
displayHeader();
?>

<!DOCTYPE html>
<html>
    <body>
        <h1> Add a New Service</h1>
        <form action="add_service.php" method = "POST">
        <label>Service Name: <input type="text" name="name" required></label><br>
        <label>Description:<br>
            <textarea name="description" rows="5" cols="40" required></textarea><br>
        </label>
        <label>Category: 
            <select name="category_id" required>
                <?php foreach($categories as $category):?>
                    <option value="<?php echo htmlspecialchars($category['id']);?>">
                        <?php echo htmlspecialchars($category['name']);?>
                    </option>
                    <?php endforeach ?>
            </select>
        </label><br>
        <label>Price: <input type="number" step="0.01" name="price" required></label><br>
        <input type="submit" value="Add Service">
    </form>
    </body>
</html>