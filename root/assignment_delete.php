<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

if (($_SESSION['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$pdo->prepare("DELETE FROM checklist_assignments WHERE user_id=?")->execute([$data['user_id']]);
$pdo->prepare("DELETE FROM checklist_progress WHERE user_id=?")->execute([$data['user_id']]);
