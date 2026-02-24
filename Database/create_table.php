<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS `user_tasks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `checklist_id` int(11) NOT NULL,
      `title` varchar(255) DEFAULT NULL,
      `sort_order` int(11) DEFAULT 0,
      `completed` tinyint(1) DEFAULT 0,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `checklist_id` (`checklist_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    $pdo->exec($sql);
    echo "user_tasks table created successfully!\n";

    // Verify the table was created
    $tables = $pdo->query("SHOW TABLES LIKE 'user_tasks'")->fetchAll();
    if (count($tables) > 0) {
        echo "Table verification: user_tasks exists\n";
    } else {
        echo "Error: user_tasks table was not created\n";
    }

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
