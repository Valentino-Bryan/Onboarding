<?php
ob_start();

if (!isset($pdo)) {
    require_once __DIR__ . '/../includes/db.php';
}
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

    echo "\nDatabase update completed successfully!\n";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo nl2br(htmlspecialchars($output));
?>
