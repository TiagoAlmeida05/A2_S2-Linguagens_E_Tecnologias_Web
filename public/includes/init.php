<?php
require_once 'config.php';

try {
    $db = getDB();

    // Create tables
    $db->exec(file_get_contents('schema.sql'));

    // Insert categories
    $db->exec("
        INSERT INTO Categories (name, description) VALUES
            ('Web Development', 'Services related to building websites'),
            ('Graphic Design', 'Visual and brand design services'),
            ('Content Writing', 'Writing, editing, and content creation'),
            ('SEO Optimization', 'Search engine optimization services'),
            ('Mobile App Development', 'Apps for iOS and Android')
    ");

    $users = [
        ['João Gomes', 'admin_user', 'admin@example.com', 'adminpass', '1'],
        ['João Baião', 'client_user', 'client@example.com', 'clientpass', '0'],
        ['João Silva', 'freelancer1', 'freelancer1@example.com', 'pass123', '0'],
        ['João Martins', 'freelancer2', 'freelancer2@example.com', 'pass123', '0'],
        ['João Paiva', 'freelancer3', 'freelancer3@example.com', 'pass123', '0'],
        ['João Almeida', 'freelancer4', 'freelancer4@example.com', 'pass123', '0'],
        ['João Costa', 'freelancer5', 'freelancer5@example.com', 'pass123', '0'],
    ];

    $stmt = $db->prepare("
        INSERT INTO Users (name, username, email, password, is_admin)
        VALUES (?, ?, ?, ?, ?)
    ");

    // Hash each and every user passwords
    foreach ($users as $user) {
        // Remember that "$user[2]" stores the user's password
        $hashedPassword = password_hash($user[3], PASSWORD_DEFAULT);
        // We will now create the actual user with the hashed password
        $stmt->execute([$user[0], $user[1], $user[2], $hashedPassword, $user[4]]);
    }

    // Insert services (1 for each freelancer in different categories)
    $db->exec("
        INSERT INTO Services (freelancer_id, title, description, category_id, price, delivery_time) VALUES
            (3, 'Modern Website', 'Build a modern responsive website.', 1, 500.00, 7),
            (4, 'Logo Design', 'Custom logo for your brand.', 2, 150.00, 3),
            (5, 'Blog Writing', 'SEO-friendly blog posts.', 3, 80.00, 2),
            (6, 'SEO Audit', 'Comprehensive SEO site audit.', 4, 200.00, 5),
            (7, 'iOS App', 'Develop a native iOS application.', 5, 1000.00, 14)
    ");

    echo "Database initialized successfully!";
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
    