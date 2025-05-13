<?php
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

    echo "Account created!<a href='login.html'>Login here</a>.";
} catch (PDOException $e){
    echo "Database error: ". $e->getMessage();
}

} else {
    echo "Invalid request method.";
}
?>