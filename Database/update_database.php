<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // Check if email column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    $columnExists = $stmt->rowCount() > 0;

    if (!$columnExists) {
        // Add email column
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE AFTER username");
        echo "Added 'email' column to users table.\n";
    } else {
        echo "'email' column already exists in users table.\n";
    }

    // Add created_at column if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    $columnExists = $stmt->rowCount() > 0;

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "Added 'created_at' column to users table.\n";
    } else {
        echo "'created_at' column already exists in users table.\n";
    }

    echo "Database update completed successfully!\n";

} catch (PDOException $e) {
    die("Error updating database: " . $e->getMessage() . "\n");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 2rem; }
        pre { background: #f4f4f4; padding: 1rem; border-radius: 4px; }
        .success { color: #166534; }
    </style>
</head>
<body>
    <h1>Database Update</h1>
    <p>Check the output below to see if any changes were made to the database.</p>
    <pre><?php
    // Re-run the update to show the output
    require_once 'update_database_output.php';
    ?></pre>
    <p><a href="../auth/login.php">Go to Login Page</a></p>
</body>
</html>
