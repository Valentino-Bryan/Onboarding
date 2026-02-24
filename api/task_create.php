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

$userId = (int)($data['user_id'] ?? 0);
$checklistId = (int)($data['checklist_id'] ?? 0);
$title = trim($data['title'] ?? '');

if ($userId <= 0 || $checklistId <= 0 || $title === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Ongeldige invoer']);
    exit;
}

/* next sort order */
$stmt = $pdo->prepare("
    SELECT COALESCE(MAX(sort_order), 0) + 1
    FROM user_tasks
    WHERE user_id = ? AND checklist_id = ?
");
$stmt->execute([$userId, $checklistId]);
$sortOrder = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    INSERT INTO user_tasks (user_id, checklist_id, title, sort_order, completed)
    VALUES (?, ?, ?, ?, 0)
");
$stmt->execute([$userId, $checklistId, $title, $sortOrder]);

echo json_encode([
    'success' => true,
    'id' => (int)$pdo->lastInsertId(),
    'sort_order' => $sortOrder
]);
