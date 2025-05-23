<?php
require_once 'config.php';

try {
    // Initialize DB connection
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the Users table already exists
    $check = $db->query("
        SELECT name
        FROM sqlite_master
        WHERE type='table'
          AND name='Users'
        LIMIT 1
    ")->fetch();

    if (! $check) {
        // --- first‐time setup: schema + seeds ---

        // Load & exec schema
        $schemaFile = __DIR__ . '/schema.sql';
        if (! file_exists($schemaFile)) {
            throw new RuntimeException("Could not find schema file at $schemaFile");
        }
        $schemaSql = file_get_contents($schemaFile);
        if ($schemaSql === false) {
            throw new RuntimeException("Failed to read schema file at $schemaFile");
        }
        $db->exec($schemaSql);

        // Seed Categories
        $db->exec("
            INSERT INTO Categories (name, description) VALUES
                ('Web Development', 'Services related to building websites'),
                ('Graphic Design', 'Visual and brand design services'),
                ('Content Writing', 'Writing, editing, and content creation'),
                ('SEO Optimization', 'Search engine optimization services'),
                ('Mobile App Development', 'Apps for iOS and Android')
        ");

        // Prepare Users seed
        $users = [
            ['João Gomes',    'admin_user',      'admin@example.com',      'adminpass',   '1'],
            ['João Baião',    'client_user',     'client@example.com',     'clientpass',  '0'],
            ['João Silva',    'freelancer1',     'freelancer1@example.com','pass123',     '0'],
            ['João Martins',  'freelancer2',     'freelancer2@example.com','pass123',     '0'],
            ['João Paiva',    'freelancer3',     'freelancer3@example.com','pass123',     '0'],
            ['João Almeida',  'freelancer4',     'freelancer4@example.com','pass123',     '0'],
            ['João Costa',    'freelancer5',     'freelancer5@example.com','pass123',     '0'],
        ];
        $stmt = $db->prepare("
            INSERT INTO Users (name, username, email, password, is_admin)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($users as $u) {
            $hashed = password_hash($u[3], PASSWORD_DEFAULT);
            $stmt->execute([$u[0], $u[1], $u[2], $hashed, $u[4]]);
        }

        // Seed Services
        $db->exec("
            INSERT INTO Services (freelancer_id, title, description, category_id, price, delivery_time) VALUES
                (3, 'Modern Website',   'Build a modern responsive website.', 1, 500.00, 7),
                (4, 'Logo Design',      'Custom logo for your brand.',      2, 150.00, 3),
                (5, 'Blog Writing',     'SEO-friendly blog posts.',         3,  80.00, 2),
                (6, 'SEO Audit',        'Comprehensive SEO site audit.',    4, 200.00, 5),
                (7, 'iOS App',          'Develop a native iOS application.',5,1000.00,14)
        ");

        echo "Database schema created and seeded successfully.";
    } else {
        // Already initialized
        // (you can comment out the echo if you don't want a message on every request)
        // echo "Database already initialized, skipping schema & seeds.";
    }
} catch (Exception $e) {
    die("Error during initialization: " . $e->getMessage());
}
