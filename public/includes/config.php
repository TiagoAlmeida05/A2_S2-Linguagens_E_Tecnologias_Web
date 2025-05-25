<?php
// Define site_url and db_path variable
define('SITE_URL', '');
define('DB_PATH', __DIR__ . '/../../project.db');

// Session settings
session_start();
?> 

<?php
function getDB() {
    try {
        // 1. Connect to the SQLite database file
        $db = new PDO('sqlite:' . DB_PATH);
        
        // 2. Make PDO throw exceptions on errors (safer than silent failures)
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 3. Enable foreign key checks (enforce table relationships)
        $db->exec('PRAGMA foreign_keys = ON;');
        
        // 4. Return the configured database connection
        return $db;
    } catch (PDOException $e) {
        // 5. If connection fails, show error and stop the script
        die("Database connection failed: " . $e->getMessage());
    }
}
?> 