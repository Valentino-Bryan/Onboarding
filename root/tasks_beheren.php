<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/navigation.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: onboarding.php');
    exit();
}

$errors = [];
$success = '';

// Get all checklists for dropdown
$checklists = $pdo->query("SELECT * FROM checklists ORDER BY title")->fetchAll();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $checklist_id = (int)($_POST['checklist_id'] ?? 0);

    if ($action === 'delete' && $id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM checklist_items WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Taak verwijderd.';
        } catch (PDOException $e) {
            $errors[] = 'Fout bij verwijderen: ' . $e->getMessage();
        }
    } elseif ($action === 'create' && $title && $checklist_id) {
        try {
            // Get next sort order
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM checklist_items WHERE checklist_id = ?");
            $stmt->execute([$checklist_id]);
            $sortOrder = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("INSERT INTO checklist_items (checklist_id, title, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$checklist_id, $title, $sortOrder]);
            $success = 'Taak toegevoegd.';
        } catch (PDOException $e) {
            $errors[] = 'Fout bij toevoegen: ' . $e->getMessage();
        }
    } elseif ($action === 'update' && $id && $title) {
        try {
            $stmt = $pdo->prepare("UPDATE checklist_items SET title = ? WHERE id = ?");
            $stmt->execute([$title, $id]);
            $success = 'Taak bijgewerkt.';
        } catch (PDOException $e) {
            $errors[] = 'Fout bij bewerken: ' . $e->getMessage();
        }
    }
}

// Get selected checklist from URL parameter
$selected_checklist_id = isset($_GET['checklist_id']) ? (int)$_GET['checklist_id'] : 0;
$tasks = [];
$selected_checklist = null;

if ($selected_checklist_id) {
    // Get checklist info
    $stmt = $pdo->prepare("SELECT * FROM checklists WHERE id = ?");
    $stmt->execute([$selected_checklist_id]);
    $selected_checklist = $stmt->fetch();

    // Get tasks for this checklist
    $stmt = $pdo->prepare("SELECT * FROM checklist_items WHERE checklist_id = ? ORDER BY sort_order, id");
    $stmt->execute([$selected_checklist_id]);
    $tasks = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Taken Beheren</title>
    <link rel="stylesheet" href="../assets/css/login.css"/>
    <link rel="stylesheet" href="../assets/css/admin_nav.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        .checklist-selector {
            margin-bottom: 24px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .checklist-selector select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            background: white;
        }
        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .task-info {
            flex: 1;
        }
        .task-title {
            font-weight: 500;
            margin-bottom: 4px;
        }
        .task-order {
            color: #6b7280;
            font-size: 14px;
        }
        .task-actions {
            display: flex;
            gap: 8px;
        }
        .btn-edit, .btn-delete {
            padding: 8px 14px;
            font-size: 0.85rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-edit {
            background: #f3f4f6;
            color: #374151;
        }
        .btn-delete {
            background: #dc2626;
            color: #fff;
        }
        .no-tasks {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .edit-form {
            display: none;
            margin-top: 12px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            width: 100%;
            box-sizing: border-box;
        }
        .edit-form.active {
            display: block;
        }
        .edit-form form {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            width: 100%;
        }
        .edit-form input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-save {
            padding: 8px 16px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-cancel {
            padding: 8px 16px;
            background: #6b7280;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-form {
            display: inline-block;
        }
    </style>
</head>
<body>
<script>
function toggleEditForm(taskId) {
    const form = document.getElementById('edit-form-' + taskId);
    form.classList.toggle('active');
}
</script>
<main>
    <div class="login-box" style="max-width: none; width: 95%; margin: 20px auto;">
        <h1>Taken Beheren</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <!-- Checklist Selector -->
        <div class="checklist-selector">
            <label for="checklist-select" style="display: block; margin-bottom: 8px; font-weight: 500;">Selecteer Afvinklijst:</label>
            <select id="checklist-select" onchange="window.location.href='?checklist_id=' + this.value">
                <option value="">-- Kies een afvinklijst --</option>
                <?php foreach ($checklists as $checklist): ?>
                    <option value="<?= $checklist['id'] ?>" <?= $selected_checklist_id == $checklist['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($checklist['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selected_checklist): ?>
            <!-- Add Task Form -->
            <h2 style="font-size:20px;margin:0 0 20px 0;color:#374151;">
                Nieuwe taak voor "<?= htmlspecialchars($selected_checklist['title']) ?>"
            </h2>

            <form method="POST" action="" novalidate>
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="checklist_id" value="<?= $selected_checklist_id ?>">
                <div class="form-group">
                    <label for="title">Taak titel</label>
                    <input id="title" name="title" type="text" placeholder="Nieuwe taak" required>
                </div>
                <button type="submit">Toevoegen</button>
            </form>

            <!-- Tasks List -->
            <?php if (!empty($tasks)): ?>
                <div style="margin-top:24px;">
                    <h2 style="font-size:18px;margin:0 0 16px 0;color:#374151;">Taken in deze lijst</h2>
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-item">
                            <div class="task-info">
                                <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
                                <div class="task-order">Volgorde: <?= $task['sort_order'] ?></div>
                            </div>
                            <div class="task-actions">
                                <button class="btn-edit" onclick="toggleEditForm(<?= $task['id'] ?>)">
                                    <span class="material-symbols-outlined">edit</span>
                                    Bewerken
                                </button>

                                <!-- Edit Form (Hidden by default) -->
                                <div id="edit-form-<?= $task['id'] ?>" class="edit-form">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                        <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" placeholder="Taak titel" required>
                                        <button type="submit" class="btn-save">Opslaan</button>
                                        <button type="button" onclick="toggleEditForm(<?= $task['id'] ?>)" class="btn-cancel">Annuleren</button>
                                    </form>
                                </div>

                                <!-- Delete Form (shown when not editing) -->
                                <form method="POST" action="" class="delete-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Weet je zeker dat je deze taak wilt verwijderen?')">
                                        <span class="material-symbols-outlined">delete</span>
                                        Verwijderen
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-tasks">
                    <span class="material-symbols-outlined" style="font-size: 48px; margin-bottom: 16px;">task_alt</span>
                    <p>Nog geen taken in deze afvinklijst.</p>
                    <p>Voeg je eerste taak hierboven toe.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-tasks">
                <span class="material-symbols-outlined" style="font-size: 48px; margin-bottom: 16px;">checklist</span>
                <p>Selecteer een afvinklijst om taken te beheren.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>© Technolab Leiden | Admin</p>
</footer>
</body>
</html>
