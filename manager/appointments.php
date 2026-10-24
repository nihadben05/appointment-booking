<?php
$base = '../';
$required_role = 'manager';
require_once "../includes/auth.php";

$status = $_GET['status'] ?? '';
if (!in_array($status, ['pending', 'approved', 'cancelled'], true)) {
    $status = '';
}

handle_manager_action('appointments.php' . ($status !== '' ? '?status=' . $status : ''));

$sql = "
SELECT a.id, u.name, s.`date`, s.`time`, a.status
FROM appointments a
JOIN users u ON a.user_id = u.id
JOIN appointment_slots s ON a.slot_id = s.id
";
$params = [];

if ($status !== '') {
    $sql .= " WHERE a.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY s.`date`, s.`time`";

$rows = db_all($sql, $params);

$title = "Booking Requests";
include "../includes/header.php";
?>
<header class="header">
    <h2>Booking Requests</h2>
    <a href="dashboard.php" class="home">Home</a>
</header>

<?= flash_html() ?>

<table>
<tr>
    <th>Client</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php foreach ($rows as $r): ?>
<tr>
    <td><?= e($r['name']) ?></td>
    <td><?= e($r['date']) ?></td>
    <td><?= e($r['time']) ?></td>
    <td>
        <span class="status <?= e($r['status']) ?>">
            <?= e(status_label($r['status'])) ?>
        </span>
    </td>

    <td class="actions">
    <?php if ($r['status'] === 'pending'): ?>
        <form method="POST">
            <?= csrf_field() ?>
            <button class="approve" name="approve" value="<?= (int)$r['id'] ?>">✔ Approve</button>
            <button class="cancel" name="cancel" value="<?= (int)$r['id'] ?>">✖ Reject</button>
        </form>
    <?php else: ?>
        <span class="no-action">—</span>
    <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

<?php if (!$rows): ?>
<tr>
    <td colspan="5"><span class="no-action">No booking requests found.</span></td>
</tr>
<?php endif; ?>
</table>

<?php include "../includes/footer.php"; ?>
