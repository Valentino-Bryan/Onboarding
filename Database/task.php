<?php

require_once __DIR__ . '/../includes/db.php';

print_r($_GET);

$stmt = $pdo->prepare("
    UPDATE user_tasks SET completed = ? WHERE user_tasks.user_id = ? AND id = ?
");
$stmt->execute([$_GET['completed'], $_GET['user_id'], $_GET['task_id']]);