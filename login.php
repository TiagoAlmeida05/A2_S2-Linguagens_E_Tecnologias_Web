<?php
session_start();
try{
    $db = new PDO('sqlite:workhive.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = $user['is_admin'];
            header("Location: profile.php");
            exit();
        }else{
            echo "Invalid username or password.";
        }
    }
} catch (PDOException $e){
    echo "Database error: ". $e->getMessage();
}
?>