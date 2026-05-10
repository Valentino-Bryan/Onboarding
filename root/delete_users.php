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

// Handle POST requests for user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($action === 'delete' && $user_id) {
        // Prevent deleting the current admin user
        if ($user_id === $_SESSION['user_id']) {
            $errors[] = 'Je kunt jezelf niet verwijderen.';
        } else {
            try {
                
                $pdo->prepare("DELETE FROM user_tasks WHERE user_id = ?")->execute([$user_id]);
                $pdo->prepare("DELETE FROM checklist_progress WHERE user_id = ?")->execute([$user_id]);
                $pdo->prepare("DELETE FROM checklist_assignments WHERE user_id = ?")->execute([$user_id]);
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

                $success = 'Gebruiker succesvol verwijderd.';
            } catch (PDOException $e) {
                $errors[] = 'Fout bij verwijderen: ' . $e->getMessage();
            }
        }
    }
}

// pakt alle users
$users = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY username")->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikers Verwijderen</title>
    <link rel="stylesheet" href="../assets/css/login.css"/>
    <link rel="stylesheet" href="../assets/css/admin_nav.css"/>
    <link rel="stylesheet" href="../assets/css/delete_users.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
<main>
    <div class="login-box" style="max-width: none; width: 95%; margin: 20px auto;">
        <h1>Gebruikers Verwijderen</h1>

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

        <?php if (empty($users)): ?>
            <div class="empty-message">
                <p>Geen gebruikers gevonden.</p>
            </div>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Gebruikersnaam</th>
                        <th>E-mail</th>
                        <th>Rol</th>
                        <th>Aangemaakt op</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="Gebruikersnaam"><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                            <td data-label="E-mail"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                            <td data-label="Rol">
                                <span class="role-badge <?php echo htmlspecialchars($user['role']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                </span>
                            </td>
                            <td data-label="Aangemaakt op"><?php echo date('d-m-Y H:i', strtotime($user['created_at'])); ?></td>
                            <td data-label="Actie">
                                <button 
                                    type="button" 
                                    class="delete-btn"
                                    onclick="openModal(<?php echo (int)$user['id']; ?>, <?php echo ($user['id'] === $_SESSION['user_id']) ? 'true' : 'false'; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                    <?php echo ($user['id'] === $_SESSION['user_id']) ? 'disabled title="Je kunt jezelf niet verwijderen"' : ''; ?>
                                >
                                    Verwijderen
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<!-- Custom Confirmation Model -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Gebruiker verwijderen</h2>
        </div>
        <div class="modal-body">
            Weet je zeker dat je deze gebruiker wilt verwijderen?
            <strong id="modalUsername"></strong>
            <p style="color: #dc2626; margin-top: 8px; font-size: 12px;">⚠️ Deze actie kan niet ongedaan gemaakt worden.</p>
        </div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Annuleren</button>
            <button class="modal-btn modal-btn-confirm" onclick="confirmDelete()">Verwijderen</button>
        </div>
    </div>
</div>


<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="deleteUserId">
</form>

<script>
    let currentDeleteUserId = null;
    let isCurrentUser = false;

    function openModal(userId, isCurrentUser, username) {
        if (isCurrentUser) {
            return; // Button is disabled, but just in case
        }
        currentDeleteUserId = userId;
        document.getElementById('modalUsername').textContent = username;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.remove('active');
        currentDeleteUserId = null;
    }

    function confirmDelete() {
        if (currentDeleteUserId) {
            document.getElementById('deleteUserId').value = currentDeleteUserId;
            document.getElementById('deleteForm').submit();
        }
    }

    // Close modal when clicking outside of it
    document.getElementById('deleteModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>
</body>
</html>
