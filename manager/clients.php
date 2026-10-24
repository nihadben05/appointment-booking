<?php
$base = '../';
$required_role = 'manager';
require_once "../includes/auth.php";

$clients = db_all("SELECT id, name, email, phone FROM users WHERE role='client' ORDER BY id");

$title = "Client List";
include "../includes/header.php";
?>

<header class="header">
    <h2>Client List</h2>
    <a href="dashboard.php" class="home">Home</a>
</header>

<?= flash_html() ?>

<table>
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Details</th>
</tr>

<?php foreach ($clients as $i => $c): ?>
<tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($c['name']) ?></td>
    <td><?= e($c['email']) ?></td>
    <td><?= e($c['phone']) ?></td>
    <td>
        <a class="view-btn" href="client_info.php?id=<?= (int)$c['id'] ?>">👁 View</a>
    </td>
</tr>
<?php endforeach; ?>

<?php if (!$clients): ?>
<tr>
    <td colspan="5"><span class="no-action">No clients yet.</span></td>
</tr>
<?php endif; ?>
</table>

<?php include "../includes/footer.php"; ?>
