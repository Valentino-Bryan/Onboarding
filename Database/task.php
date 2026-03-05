<?php

require_once __DIR__ . '/../includes/db.php';

print_r($_GET);

$stmt = $pdo->prepare("
    INSERT INTO checklist_progress (user_id, checklist_item_id, completed) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE completed = VALUES(completed)
");
$stmt->execute([$_GET['user_id'], $_GET['task_id'], $_GET['completed']]);