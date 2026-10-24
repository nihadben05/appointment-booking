<?php
$base = '../';
$required_role = 'manager';
require_once "../includes/auth.php";

$id = (int)($_GET['id'] ?? 0);
$c = db_one("SELECT name, email, phone FROM users WHERE id=? AND role='client'", [$id]);

if (!$c) {
    flash('error', 'Client not found.');
    redirect("clients.php");
}

$title = "Client Information";
include "../includes/header.php";
?>
<header class="header">
    <h2>Client Information</h2>
    <a href="clients.php" class="home">⬅ Back</a>
</header>

<div class="client-card">
    <p><strong>👤 Name:</strong> <?= e($c['name']) ?></p>
    <p><strong>📧 Email:</strong> <?= e($c['email']) ?></p>
    <p><strong>📞 Phone:</strong> <?= e($c['phone']) ?></p>
</div>

<?php include "../includes/footer.php"; ?>
