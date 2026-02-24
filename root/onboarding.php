<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT ci.id, ci.title,
           IF(cp.completed IS NULL, 0, cp.completed) AS completed
    FROM checklist_items ci
    JOIN checklist_assignments ca ON ca.checklist_id = ci.checklist_id
    LEFT JOIN checklist_progress cp ON cp.checklist_item_id = ci.id AND cp.user_id = ?
    WHERE ca.user_id = ?
");
$stmt->execute([$userId, $userId]);
$items = $stmt->fetchAll();

// Bereken voortgang
$total = count($items);
$done = array_sum(array_column($items,'completed'));
$percent = $total ? round($done/$total*100) : 0;
?>


<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Onboarding Dashboard</title>
    <script src="../assets/js/onboarding.js" defer></script>
    <link rel="stylesheet" href="../assets/css/onboarding.css" />
    <link rel="stylesheet" href="../assets/css/admin_nav.css" />
</head>

<body>

<div class="page">

    <!-- ========================= -->
    <!-- RESPONSIVE NAVIGATION BAR -->
    <!-- ========================= -->
    <header class="header">
        <nav class="nav">

            <div class="nav-left">
                <img src="../images/logo.jpg">

               
            </div>

            <div class="nav-right desktop-only">
                <span class="welcome">Welkom, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Gebruiker'); ?>!</span>
                <?php if (($_SESSION['role'] ?? 'user') === 'admin'): ?>
                    <a href="admin_hub.php" class="logout-btn">Admin Hub</a>
                <?php endif; ?>
                <a href="../auth/logout.php" class="logout-btn" onclick="showLogoutModal(event, '../auth/logout.php')">Uitloggen</a>
            </div>

            <!-- Hamburger -->
            <div class="hamburger" id="hamburger">
                <span></span><span></span><span></span>
            </div>

        </nav>

        <!-- Mobile dropdown -->
        <div class="mobile-menu" id="mobileMenu">
            <a href="onboarding.php">Onboarding</a>
            <a href="toewijzen.php">Toewijzen</a>
            <?php if (($_SESSION['role'] ?? 'user') === 'admin'): ?>
                <a href="admin_hub.php">Admin Hub</a>
            <?php endif; ?>
            <a href="../auth/logout.php" class="mobile-logout" onclick="showLogoutModal(event, '../auth/logout.php')">Uitloggen</a>
        </div>
    </header>

    <!-- ========================= -->
    <!-- MAIN CONTENT -->
    <!-- ========================= -->
    <main class="main">
        <div class="layout">

            <div class="progress-card" data-complete="<?= $percent === 100 ? 'true' : 'false' ?>">

                <div class="progress-wrapper">
                    <svg viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="54" class="progress-bg"></circle>
                        <circle cx="60" cy="60" r="54" class="progress-bar"></circle>
                    </svg>

                    <div class="progress-center">
                        <span class="progress-num">0%</span>
                        <span class="progress-text">voltooid</span>
                    </div>
                </div>

                <div class="progress-info">
                    <h2>Jouw Voortgang</h2>
                    <p>Laten we je account instellen. Voltooi alle stappen om te beginnen.</p>
                </div>
            </div>

            <div class="checklist">
                <?php foreach($items as $item): ?>
                    <div class="checklist-item">
                        <div class="left">
                            <div class="circle"></div>
                            <span><?= ($item['title']) ?></span>
                        </div>
                        <button class="btn" id="detailsBtn" >Bekijk details</button>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>

    <!-- ========================= -->
    <!-- FOOTER -->
    <!-- ========================= -->
    <footer class="footer">
        <p> Technolab Leiden | Onboarding - Safouane</p>
    </footer>

</div>

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
</body>
</html>
