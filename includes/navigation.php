<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="admin-nav">
    <span class="brand">Admin</span>
    <a href="admin_hub.php" <?= $current_page === 'admin_hub.php' ? 'class="current"' : '' ?>>Hub</a>
    <a href="connect_emails.php" <?= $current_page === 'connect_emails.php' ? 'class="current"' : '' ?>>E-mails koppelen</a>
    <a href="toewijzen.php" <?= $current_page === 'toewijzen.php' ? 'class="current"' : '' ?>>Toewijzen</a>
    <a href="checklist.php" <?= $current_page === 'checklist.php' ? 'class="current"' : '' ?>>Checklist</a>
    <a href="afvinklijsten_beheren.php" <?= $current_page === 'afvinklijsten_beheren.php' ? 'class="current"' : '' ?>>Afvinklijsten</a>
    <a href="tasks_beheren.php" <?= $current_page === 'tasks_beheren.php' ? 'class="current"' : '' ?>>Taken</a>
    <a href="onboarding.php" <?= $current_page === 'onboarding.php' ? 'class="current"' : '' ?>>Onboarding</a>
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
