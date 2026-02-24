<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$id = (int)($data['id'] ?? 0);
$completed = $data['completed'] ?? null;

if ($id <= 0 || !in_array($completed, [0, 1], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ongeldige invoer']);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE user_tasks
    SET completed = ?
    WHERE id = ?
");
$stmt->execute([(int)$completed, $id]);

echo json_encode(['success' => true]);
