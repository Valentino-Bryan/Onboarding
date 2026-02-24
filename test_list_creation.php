<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: onboarding.php');
    exit();
}

// Test creating a new list
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title) {
        try {
            $stmt = $pdo->prepare("INSERT INTO checklists (title, description) VALUES (?, ?)");
            $stmt->execute([$title, $description]);
            echo "List created successfully! ID: " . $pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Show existing lists
$checklists = $pdo->query("SELECT * FROM checklists ORDER BY title")->fetchAll();
echo "<h3>Existing lists:</h3>";
foreach ($checklists as $list) {
    echo "- " . htmlspecialchars($list['title']) . " (ID: " . $list['id'] . ")<br>";
}
?>

<form method="POST">
    <h2>Create New List</h2>
    <input type="text" name="title" placeholder="List title" required><br><br>
    <textarea name="description" placeholder="Optional description"></textarea><br><br>
    <button type="submit">Create List</button>
</form>

<a href="root/afvinklijsten_beheren.php">Go to original page</a>
