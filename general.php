<?php function displayHeader(){ ?>
    <!DOCTYPE html>
        <html lang="en-US"> 
    <head>
        <meta charset="utf-8">
        <title>WorkHive</title>
    </head>
    <body>
        <header>
            <h1><a href="homepage.php">WorkHive</a></h1>
        </header>
        <?php
        if (basename($_SERVER['PHP_SELF']) !== "register.php"){
            if(isset($_SESSION['user_id'])){
                echo "<a href= 'profile.php'>Profile</a>";
                echo " | <a href= 'logout.php'>Logout</a>";
            }else{
                drawLoginForm();                    
            }
        }
        ?>
        <hr>
    <main>
<?php } ?>

<?php function displayFooter(){ ?>
    <main>
    <footer>
    <hr>
        <p>&copy; 2025 WorkHive. All rights reserved.</p>
    </footer>
    </body>
    </html>
<?php } ?>

<?php function drawLoginForm(){?>
    <form action = "login.php" method = "post">
    <input type="text" id="username" name="username" placeholder="Username" required>
    <input type="password" id="password"name="password" placeholder="Password" required>
    <input type="submit" value="Login">
    <a href="register.php">Register</a>
  </form>
<?php } ?>

<?php function displayServices(){
    try{
        $db = new PDO('sqlite:workhive.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $db->query("SELECT jobs.*, categories.name AS category_name, users.username AS listed_by
            FROM jobs
            JOIN categories ON jobs.category_id = categories.id
            Join users ON jobs.user_id = users.id");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(empty($services)){
            echo "<p>No services available.</p>";
            return;
        }

        echo "<h2>Available Services:</h2>";
        echo "<div class='services-container'>";

        foreach($services as $service){ ?>
                <div class="service-card" style="border:1px solid #ccc; padding:10px; margin-bottom:15px; border-radius:6px;">                <h3><?php echo htmlspecialchars($service['name'])?></h3>
                <p><strong>Category:</strong><?php echo htmlspecialchars($service['category_name'])?></p>
                <p><strong>Price:</strong><?php echo htmlspecialchars($service['price'])?></p>
                <p><strong>Description:</strong><?php echo htmlspecialchars($service['description'])?></p>
                <p><strong>Listed by:</strong><?php echo htmlspecialchars($service['listed_by'])?></p>
            </div>
        <?php }   
    } catch(PDOEXCEPTION $e){
        echo "<p>Database error: ".$e->getMessage(). "</p>";
    }
}
?>