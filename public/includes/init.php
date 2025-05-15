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

    // Insert users
    $db->exec("
        INSERT INTO Users (username, email, password, role) VALUES
            ('admin_user', 'admin@example.com', 'adminpass', 'admin'),
            ('client_user', 'client@example.com', 'clientpass', 'client'),
            ('freelancer1', 'freelancer1@example.com', 'pass123', 'freelancer'),
            ('freelancer2', 'freelancer2@example.com', 'pass123', 'freelancer'),
            ('freelancer3', 'freelancer3@example.com', 'pass123', 'freelancer'),
            ('freelancer4', 'freelancer4@example.com', 'pass123', 'freelancer'),
            ('freelancer5', 'freelancer5@example.com', 'pass123', 'freelancer')
    ");

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
    