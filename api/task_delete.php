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
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ongeldige taak id']);
    exit;
}

$stmt = $pdo->prepare("
    DELETE FROM user_tasks
    WHERE id = ?
");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
