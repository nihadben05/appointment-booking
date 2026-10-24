<?php
$base = '../';
$required_role = 'manager';
require_once "../includes/auth.php";

$tab = $_GET['tab'] ?? 'slots';
if (!in_array($tab, ['slots', 'requests'], true)) {
    $tab = 'slots';
}

if (isset($_POST['add_slot'])) {
    if (!csrf_valid()) {
        flash('error', 'Invalid request. Please try again.');
    } else {
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';

        $d = DateTime::createFromFormat('!Y-m-d', $date);
        $t = DateTime::createFromFormat('!H:i', $time) ?: DateTime::createFromFormat('!H:i:s', $time);

        if (!$d || $d->format('Y-m-d') !== $date || !$t) {
            flash('error', 'Please enter a valid date and time.');
        } else {
            $time = $t->format('H:i:s');
            if (db_one("SELECT id FROM appointment_slots WHERE `date`=? AND `time`=?", [$date, $time])) {
                flash('error', 'This slot already exists.');
            } else {
                db_exec("INSERT INTO appointment_slots (`date`, `time`) VALUES (?, ?)", [$date, $time]);
                flash('success', 'Slot added.');
            }
        }
    }
    redirect('dashboard.php?tab=slots');
}

handle_manager_action('dashboard.php?tab=requests');

$stats = db_one("
    SELECT COUNT(*) AS total,
           COALESCE(SUM(status='pending'), 0)   AS pending,
           COALESCE(SUM(status='approved'), 0)  AS approved,
           COALESCE(SUM(status='cancelled'), 0) AS cancelled
    FROM appointments
");
$clients = db_value("SELECT COUNT(*) FROM users WHERE role='client'");

$requests = db_all("
    SELECT a.id, u.name, s.`date`, s.`time`, a.status
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN appointment_slots s ON a.slot_id = s.id
    ORDER BY s.`date`, s.`time`
");

$slots = db_all("SELECT `date`, `time`, is_booked FROM appointment_slots ORDER BY `date`, `time`");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manager Dashboard</title>

<link rel="stylesheet" href="manager.css">
</head>

<body>

<div class="header">
    <h2>Manager Dashboard</h2>
    <a href="../logout.php" class="logout">Logout</a>
</div>

<?= flash_html() ?>

<div class="stats">

<a href="appointments.php?status=pending" class="card-link">
    <div class="card pending">Pending<br><b><?= (int)$stats['pending'] ?></b></div>
</a>

<a href="appointments.php?status=approved" class="card-link">
    <div class="card approved">Approved<br><b><?= (int)$stats['approved'] ?></b></div>
</a>

<a href="appointments.php?status=cancelled" class="card-link">
    <div class="card cancelled">Cancelled<br><b><?= (int)$stats['cancelled'] ?></b></div>
</a>

<a href="appointments.php" class="card-link">
    <div class="card total">Total Appointments<br><b><?= (int)$stats['total'] ?></b></div>
</a>

<a href="clients.php" class="card-link">
    <div class="card clients">Clients<br><b><?= (int)$clients ?></b></div>
</a>

</div>

<div class="actions">
    <button class="action-btn<?= $tab === 'slots' ? ' active' : '' ?>" data-target="slots">➕ Add Slots</button>
    <button class="action-btn<?= $tab === 'requests' ? ' active' : '' ?>" data-target="requests">📅 Booking Requests</button>
</div>

<div id="slots" class="action-container<?= $tab === 'slots' ? ' active' : '' ?>">
<h3>Add Slot</h3>
<form method="POST">
    <?= csrf_field() ?>
    <input type="date" name="date" required>
    <input type="time" name="time" required>
    <button name="add_slot" class="btn">Add</button>
</form>

<h3>Available Slots</h3>
<div class="table-wrap">
<table>
<tr><th>Date</th><th>Time</th><th>Booked</th></tr>
<?php foreach ($slots as $s): ?>
<tr>
<td><?= e($s['date']) ?></td>
<td><?= e($s['time']) ?></td>
<td>
<span class="status <?= $s['is_booked'] ? 'booked' : 'free' ?>">
<?= $s['is_booked'] ? 'Yes' : 'No' ?>
</span>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$slots): ?>
<tr><td colspan="3">No slots yet.</td></tr>
<?php endif; ?>
</table>
</div>
</div>

<div id="requests" class="action-container<?= $tab === 'requests' ? ' active' : '' ?>">
<h3>Booking Requests</h3>
<div class="table-wrap">
<table>
<tr><th>Client</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>

<?php foreach ($requests as $r): ?>
<tr>
<td><?= e($r['name']) ?></td>
<td><?= e($r['date']) ?></td>
<td><?= e($r['time']) ?></td>
<td>
<span class="status <?= e($r['status']) ?>-s">
    <?= e(status_label($r['status'])) ?>
</span>
</td>
<td>
<?php if ($r['status'] === 'pending'): ?>
<form method="POST" class="row-actions">
    <?= csrf_field() ?>
    <button name="approve" value="<?= (int)$r['id'] ?>" class="btn-approve">✔ Approve</button>
    <button name="cancel" value="<?= (int)$r['id'] ?>" class="btn-cancel">✖ Reject</button>
</form>
<?php else: ?> — <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$requests): ?>
<tr><td colspan="5">No booking requests yet.</td></tr>
<?php endif; ?>

</table>
</div>
</div>

<script>
const btns = document.querySelectorAll('.action-btn');
const boxes = document.querySelectorAll('.action-container');
btns.forEach(b => {
    b.onclick = () => {
        btns.forEach(x => x.classList.remove('active'));
        boxes.forEach(x => x.classList.remove('active'));
        b.classList.add('active');
        document.getElementById(b.dataset.target).classList.add('active');
    };
});
</script>

</body>
</html>
