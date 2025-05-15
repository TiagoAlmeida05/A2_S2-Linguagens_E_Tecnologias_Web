<?php
include 'general.php';
session_start();
displayHeader();
if (isset($_SESSION['user_id'])): ?>
    <a href="add_service.php">
        <button type="button">Add a Service</button>
    </a>
<?php endif;
displayServices();
displayFooter();
?>

