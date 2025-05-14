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
