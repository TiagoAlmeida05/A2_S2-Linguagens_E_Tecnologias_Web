<?php
include 'general.php';
session_start();

try {
    $db = new PDO('sqlite:workhive.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newUsername = $_POST['username'];
        $newName = $_POST["name"];
        $newSurname = $_POST["surname"];
        $newEmail = $_POST["email"];
        $newPassword = $_POST["password"];
        $newConfirmedPassword = $_POST["confirmed_password"];

        if (!empty($newPassword)) {
            if ($newPassword !== $newConfirmedPassword) {
                echo "Passwords do not match. Try again.";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET username = ?, name = ?, surname = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$newUsername, $newName, $newSurname, $newEmail, $hashedPassword, $_SESSION['user_id']]);
            }
        } else {
            $stmt = $db->prepare("UPDATE users SET username = ?, name = ?, surname = ?, email = ? WHERE id = ?");
            $stmt->execute([$newUsername, $newName, $newSurname, $newEmail, $_SESSION['user_id']]);
        }

        echo "Profile updated successfully!";
        $_SESSION['username'] = $newUsername;

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

displayHeader();
?>

<!DOCTYPE html>
<body>
    <h1>Your Profile, <?php echo htmlspecialchars($user['name']); echo " " ;echo htmlspecialchars($user['surname']);?> (<?php echo htmlspecialchars($user['username']);?>)!</h1>
    <h2>Change Your Profile!</h2>
    <form action="profile.php" method="POST">
        <label>Username: <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></label><br>
        <label>Name: <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required></label><br>
        <label>Surame: <input type="text" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required></label><br>
        <label>Email: <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></label><br>
        <label>New Password (leave blank to keep current): <input type="password" name="password"></label><br>
        <label>Confirm New Password: <input type="password" name="confirmed_password"></label><br>
        <input type="submit" value="Update Profile">
    </form>

    <a href="homepage.php">Back to Home</a>
</body>
</html>

<?php
displayFooter();
?>
