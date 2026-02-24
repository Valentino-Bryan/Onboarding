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

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = trim($_POST['emails'] ?? '');

    if ($raw === '') {
        $errors[] = 'Vul minimaal één e-mailadres in.';
    } else {
        $parts = preg_split('/[\r\n,;\s]+/', $raw);
        $emails = array_values(array_filter(array_map('trim', $parts)));

        if (empty($emails)) {
            $errors[] = 'Vul minimaal één e-mailadres in.';
        } else {
            $inserted = 0;

            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password, created_at) VALUES (?, '', NOW())");

                foreach ($emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Ongeldig e-mailadres: ' . $email;
                        continue;
                    }

                    $stmt->execute([$email]);

                    if ($stmt->rowCount() > 0) {
                        $inserted++;
                    } else {
                        $errors[] = 'E-mail is al gekoppeld.';
                    }
                }

                if (empty($errors)) {
                    $success = "E-mails gekoppeld: {$inserted}.";
                }
            } catch (PDOException $e) {
                $errors[] = 'Databasefout: ' . $e->getMessage();
            }
        }
    }
}

$connectedEmails = [];
try {
    $connectedEmails = $pdo->query("SELECT email FROM users WHERE (password = '' OR password IS NULL) AND email IS NOT NULL ORDER BY email")->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Databasefout: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>E-mails koppelen</title>
    <link rel="stylesheet" href="../assets/css/login.css"/>
    <link rel="stylesheet" href="../assets/css/admin_nav.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body>
<nav class="admin-nav">
    <span class="brand">Admin</span>
    <a href="admin_hub.php">Hub</a>
    <a href="connect_emails.php" class="current">E-mails koppelen</a>
    <a href="toewijzen.php">Toewijzen</a>
    <a href="checklist.php">Checklist</a>
    <a href="afvinklijsten_beheren.php">Afvinklijsten</a>
    <a href="onboarding.php">Onboarding</a>
    <a href="../auth/logout.php" class="logout" onclick="showLogoutModal(event, '../auth/logout.php')">Uitloggen</a>
</nav>

<!-- Logout confirmation modal -->
<div class="logout-modal" id="logoutModal">
    <div class="logout-modal-content">
        <h3>Uitloggen</h3>
        <p>Weet je zeker dat je wilt uitloggen?</p>
        <div class="logout-modal-buttons">
            <button class="btn-cancel" onclick="closeLogoutModal()">Annuleren</button>
            <button class="btn-confirm" onclick="confirmLogout()">Uitloggen</button>
        </div>
    </div>
</div>

<script>
let logoutUrl = '';

function showLogoutModal(event, url) {
    event.preventDefault();
    logoutUrl = url;
    document.getElementById('logoutModal').classList.add('show');
}

function closeLogoutModal() {
    document.getElementById('logoutModal').classList.remove('show');
}

function confirmLogout() {
    window.location.href = logoutUrl;
}
</script>

    <main>
        <div class="login-box">
            <h1>E-mails koppelen</h1>

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

            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="emails">E-mails (gescheiden door komma of spatie)</label>
                    <input id="emails" name="emails" type="text" placeholder="naam@bedrijf.nl" required>
                </div>
                <button type="submit">Koppelen</button>
            </form>

            <?php if (!empty($connectedEmails)): ?>
                <div style="margin-top:18px; text-align:left;">
                    <h2 style="font-size:16px;margin:0 0 8px 0;">Gekoppelde e-mails (nog geen wachtwoord)</h2>
                    <div style="max-height:180px;overflow:auto;border-radius:10px;border:1px solid #d1d5db;padding:10px;background:#fff;">
                        <?php foreach ($connectedEmails as $row): ?>
                            <div style="padding:6px 4px;border-bottom:1px solid #f3f4f6;">
                                <?php echo htmlspecialchars($row['email']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>


</body>
</html>
