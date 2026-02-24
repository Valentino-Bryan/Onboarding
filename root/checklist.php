<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/navigation.php';

/* AUTHENTICATIE */
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: onboarding.php');
    exit;
}

/* INPUT */
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

/* GEBRUIKERS */
$users = $pdo->query("
    SELECT id, username, email
    FROM users
    ORDER BY COALESCE(username, email)
")->fetchAll(PDO::FETCH_ASSOC);

$checklist = null;
$tasks = [];

if ($userId) {
    // Haal checklist voor deze gebruiker
    $stmt = $pdo->prepare("
        SELECT c.id, c.title
        FROM checklists c
        INNER JOIN checklist_assignments ca ON ca.checklist_id = c.id
        WHERE ca.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $checklist = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($checklist) {
        // Vul user_tasks als deze leeg is
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM user_tasks
            WHERE user_id = ? AND checklist_id = ?
        ");
        $stmt->execute([$userId, $checklist['id']]);
        if ((int)$stmt->fetchColumn() === 0) {
            $stmt = $pdo->prepare("
                INSERT INTO user_tasks (user_id, checklist_id, title, sort_order, completed)
                SELECT ?, checklist_id, title, COALESCE(sort_order,0), 0
                FROM checklist_items
                WHERE checklist_id = ?
                ORDER BY sort_order
            ");
            $stmt->execute([$userId, $checklist['id']]);
        }

        // Haal taken op
        $stmt = $pdo->prepare("
            SELECT id, title, completed
            FROM user_tasks
            WHERE user_id = ? AND checklist_id = ?
            ORDER BY sort_order, id
        ");
        $stmt->execute([$userId, $checklist['id']]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Checklist</title>
    <link rel="stylesheet" href="../assets/css/checklist.css">
    <link rel="stylesheet" href="../assets/css/admin_nav.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body class="page">
<div class="layout">
    <main class="main">
        <div class="top-bar">
            <div class="title-block">
                <h2>Checklist beheren</h2>
            </div>
            <div class="user-select">
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">search</span>
                    <input id="user-search" placeholder="Zoek gebruiker" value="<?= $userId ? htmlspecialchars($users[array_search($userId, array_column($users, 'id'))]['username'] ?: $users[array_search($userId, array_column($users, 'id'))]['email']) : '' ?>">
                </div>
                <div class="dropdown" id="userDropdown">
                    <?php foreach ($users as $u): ?>
                        <div class="dropdown-item" data-id="<?= $u['id'] ?>">
                            <?= htmlspecialchars($u['username'] ?: $u['email']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <section id="taskSection" class="task-section"
                 data-user-id="<?= (int)$userId ?>"
                 data-checklist-id="<?= (int)($checklist['id'] ?? 0) ?>">
            <?php if (!$userId): ?>
                <p>Selecteer een gebruiker</p>
            <?php elseif (!$checklist): ?>
                <p>Geen checklist toegewezen</p>
            <?php else: ?>
                <h3><?= htmlspecialchars($checklist['title']) ?></h3>
                <div id="taskList">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-item" data-id="<?= $task['id'] ?>">
                            <span class="material-symbols-outlined drag-icon">drag_indicator</span>
                            <input type="checkbox" class="task-complete" <?= $task['completed'] ? 'checked' : '' ?>>
                            <p class="task-title"><?= htmlspecialchars($task['title']) ?></p>
                            <button class="edit" title="Bewerken">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="delete" title="Verwijderen">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="add-task-btn" id="addTaskBtn">
                    <span class="material-symbols-outlined">add</span>
                    Nieuwe taak
                </button>
            <?php endif; ?>
        </section>
    </main>
</div>

<script src="../assets/js/checklist.js"></script>
</body>
</html>
