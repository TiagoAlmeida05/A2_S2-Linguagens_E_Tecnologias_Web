<?php 
include 'general.php';
displayHeader();
?>
<!DOCTYPE html>
<html>
  <h2>Create an Account!</h2>
  <form action ="register.php" method = "post">
    <label for ="name">First Name:</label><br>
    <input type="text" id="name" name="name" required><br><br>
    <label for ="surname">Last Name:</label><br>
    <input type="text" id="surname" name="surname" required><br><br>
    <label for ="email">Email:</label><br>
    <input type="text" id="email" name="email" required><br><br>
    <label for ="username">Username:</label><br>
    <input type="text" id="username" name="username" required><br><br>
    <label for="password">Password:</label><br>
    <input type="password" id="password"name="password" required><br><br>
    <label for="password">Confirm Password:</label><br>
    <input type="password" id="confirmed_password" name="confirmed_password" required><br><br>
    <input type="submit" value="Create Account">
  </form>
  <p>Already have an account? <a href="homepage.php">Login!</a></p>

</html>
<?php
displayFooter();

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $firstName = $_POST['name'];
    $lastName = $_POST['surname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmedPassword = $_POST['confirmed_password'];

    if($password !== $confirmedPassword){
        echo "Passwords do not match. Try again.";
        exit();
    }


$password = password_hash($password, PASSWORD_DEFAULT);

try{
    $db = new PDO('sqlite:workhive.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();

    if($count > 0){
        echo "Username already taken. Choose a different one.";
        exit();
    }

    $stmt = $db->prepare("INSERT INTO users (username,password, name, surname, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $firstName, $lastName, $email]);

    echo "Account created!<a href='homepage.php'>Login here</a>.";
} catch (PDOException $e){
    echo "Database error: ". $e->getMessage();
}
}
?>