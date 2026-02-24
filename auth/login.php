<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../root/onboarding.php');
    exit();
}

require_once __DIR__ . '/../includes/db.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $isAdminIdentifier = strcasecmp($email, 'admin') === 0;

    if (empty($email) || empty($password)) {
        $error = 'Vul zowel e-mail als wachtwoord in.';
    } elseif (!$isAdminIdentifier && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ongeldig e-mailadres.';
    } else {
        try {
            // Check if user exists
            if ($isAdminIdentifier) {
                $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");
                $stmt->execute();
            } else {
                $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
                $stmt->execute([$email]);
            }
            $user = $stmt->fetch();

            if ($isAdminIdentifier && !$user) {
                $error = 'Geen admin-account gevonden in de database.';
            } else {

                $passwordOk = false;
                if ($user) {
                    $storedPassword = (string)($user['password'] ?? '');
                    $isHashed = str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$') || str_starts_with($storedPassword, '$argon2');

                    if ($isHashed) {
                        $passwordOk = password_verify($password, $storedPassword);
                    } else {
                        $passwordOk = hash_equals($storedPassword, (string)$password);
                        if ($passwordOk) {
                            $rehash = password_hash($password, PASSWORD_DEFAULT);
                            $rehashStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $rehashStmt->execute([$rehash, $user['id']]);
                        }
                    }
                }

                if ($user && $passwordOk) {
                    // Password is correct, start session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'] ?: ($user['email'] ?: $email);
                    $_SESSION['role'] = $user['role'];

                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    if (($_SESSION['role'] ?? 'user') === 'admin') {
                        header('Location: ../root/admin_hub.php');
                        exit();
                    }

                    // Redirect to onboarding
                    header('Location: ../root/onboarding.php');
                    exit();
                } else {
                    $error = 'Ongeldig e-mailadres of wachtwoord.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Er is een fout opgetreden. Probeer het later opnieuw.';
            // For debugging: $error = 'Databasefout: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Technolab Login</title>
    <link rel="stylesheet" href="../assets/css/login.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body>
    <div class="container">
        <header>
            <img alt="Technolab Logo" class="logo" src="../images/logo_technolab 1.svg"/>
        </header>

        <main>
            <div class="login-box">
                <h1>Inloggen</h1>
                <?php if ($error): ?>
                    <div class="error-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($email); ?>" required autofocus/>
                    </div>
                    <div class="form-group">
                        <label for="password">Wachtwoord:</label>
                        <input id="password" name="password" type="password" required/>
                    </div>
                    <button type="submit">Log in</button>
                </form>
                <div class="links">
                    <a href="registreren.php">Account aanmaken</a>
                </div>
            </div>
        </main>

        <footer>
            <p>© Technolab Leiden | Onboarding - Chahid</p>
        </footer>
    </div>
</body>
</html>
