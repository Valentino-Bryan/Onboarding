<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: onboarding.php');
    exit();
}


// ================= FORM VERWERKEN =================
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $checklist_id = (int)($_POST["checklist_id"] ?? 0);
    $users = $_POST["users"] ?? [];

    if ($checklist_id && !empty($users)) {
        // insert assignment (named params ok here, single usage)
        $assignStmt = $pdo->prepare("
            INSERT IGNORE INTO checklist_assignments (user_id, checklist_id)
            VALUES (:user_id, :checklist_id)
        ");

        // copy template items into user_tasks for this user if not already present
        // NOTE: we use positional placeholders (?, ?, ?, ?) because reusing the same named parameter
        // multiple times can cause "Invalid parameter number" with some PDO drivers.
        $copyStmt = $pdo->prepare("
            INSERT INTO user_tasks (user_id, checklist_id, title, sort_order, completed)
            SELECT ?, ci.checklist_id, ci.title, COALESCE(ci.sort_order, 0), 0
            FROM checklist_items ci
            WHERE ci.checklist_id = ?
              AND NOT EXISTS (
                SELECT 1 FROM user_tasks ut
                WHERE ut.user_id = ? AND ut.checklist_id = ? AND ut.title = ci.title
              )
        ");

        foreach ($users as $user_id_raw) {
            $user_id = (int)$user_id_raw;
            if (!$user_id) continue;

            // assign checklist to user
            $assignStmt->execute([
                'user_id' => $user_id,
                'checklist_id' => $checklist_id
            ]);

            // copy template items for this user if not present
            // parameter order must match the ? placeholders above:
            // 1) user_id, 2) checklist_id (for WHERE ci.checklist_id = ?),
            // 3) user_id (for NOT EXISTS ut.user_id = ?),
            // 4) checklist_id (for NOT EXISTS ut.checklist_id = ?)
            $copyStmt->execute([$user_id, $checklist_id, $user_id, $checklist_id]);
        }

        $success = true;
    }
}

// ================= DATA OPHALEN =================
$checklists = $pdo->query("SELECT id, title FROM checklists ORDER BY title")->fetchAll();
$users = $pdo->query("SELECT id, username, email FROM users ORDER BY COALESCE(username, email)")->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afvinklijst Toewijzen</title>
    <link rel="stylesheet" href="../assets/css/toewijzen.css">
    <link rel="stylesheet" href="../assets/css/admin_nav.css">
</head>
<body>

<?php require_once __DIR__ . '/../includes/navigation.php'; ?>



<main class="container">
    <h2 class="title">Afvinklijst Toewijzen</h2>

    <?php if ($success): ?>
        <p style="color:green;font-weight:700;">✔ Checklist succesvol toegewezen en items gekopieerd</p>
    <?php endif; ?>

    <form method="post">
        <div class="columns">
            <section class="card">
                <h3>Kies een Afvinklijst</h3>
                <label class="full">
                    <p class="label">Selecteer een lijst</p>
                    <div class="select-wrap">
                        <select name="checklist_id" required>
                            <option value="">-- Kies een checklist --</option>
                            <?php foreach ($checklists as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </label>
            </section>

            <section class="card">
                <h3>Selecteer Gebruikers</h3>
                <div class="search">
                    <input type="text" id="userSearch" placeholder="Zoek op naam of e-mail...">
                </div>
                <p class="select-info" id="selectedCount">0 gebruikers geselecteerd</p>
                <div class="user-list" id="userList">
                    <?php foreach ($users as $u): ?>
                        <label class="user">
                            <input type="checkbox" name="users[]" value="<?= $u['id'] ?>">
                            <div class="avatar small"></div>
                            <div>
                                <p class="user-name"><?= htmlspecialchars($u['username']) ?></p>
                                <p class="user-email"><?= htmlspecialchars($u['email'] ?? '') ?></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <div class="bottom">
            <button class="assign-btn" type="submit">Toewijzen ➜</button>
        </div>
    </form>
</main>

<script src="../assets/js/toewijzen.js"></script>
</body>
</html>
