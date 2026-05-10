<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../root/onboarding.php');
    exit();
}

require_once __DIR__ . '/../includes/db.php';

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $isAdminIdentifier = strcasecmp($login, 'admin') === 0;

    if (empty($login) || empty($password)) {
        $error = 'Vul zowel gebruikersnaam/e-mail als wachtwoord in.';
    } else {
        try {
            // Check if user exists
            if ($isAdminIdentifier) {
                $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");
                $stmt->execute();
            } else {
                $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE email = ? OR username = ?");
                $stmt->execute([$login, $login]);
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
                    $_SESSION['username'] = $user['username'] ?? $user['email'];
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
                    $error = 'Ongeldige gebruikersnaam/e-mail of wachtwoord.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Er is een fout opgetreden. Probeer het later opnieuw.';
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
                        <label for="login">Gebruikersnaam of e-mail:</label>
                        <input 
                            id="login" 
                            name="login" 
                            type="text" 
                            value="<?php echo htmlspecialchars($login); ?>" 
                            placeholder="Gebruikersnaam of e-mail"
                            required 
                            autofocus
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">Wachtwoord:</label>
                        <input id="password" name="password" type="password" required
                        placeholder="Wachtwoord"/>
                    </div>

                    <button type="submit">Log in</button>
                </form>

                <div class="links">
                    <a href="registreren.php">Account aanmaken</a>
                </div>
            </div>
        </main>

        <footer>
            <p>© Technolab Leiden | Onboarding</p>
        </footer>
    </div>
</body>
</html>