<?php
$base = '../';
$required_role = 'client';
require_once "../includes/auth.php";

$user_id = (int)$_SESSION['user_id'];

$tab = $_GET['tab'] ?? 'my';
if (!in_array($tab, ['my', 'book', 'profile'], true)) {
    $tab = 'my';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $back = 'dashboard.php?tab=my';

    if (!csrf_valid()) {
        flash('error', 'Invalid request. Please try again.');
    } elseif (isset($_POST['book'])) {
        $back = 'dashboard.php?tab=book';
        if (book_slot($user_id, (int)($_POST['slot_id'] ?? 0))) {
            flash('success', 'Your appointment request has been submitted.');
            $back = 'dashboard.php?tab=my';
        } else {
            flash('error', 'This slot is no longer available. Please choose another one.');
        }
    } elseif (isset($_POST['cancel'])) {
        if (cancel_appointment((int)$_POST['cancel'], $user_id)) {
            flash('success', 'Appointment cancelled.');
        } else {
            flash('error', 'This appointment cannot be cancelled.');
        }
    } elseif (isset($_POST['save_profile'])) {
        $back = 'dashboard.php?tab=profile';
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!valid_name($name)) {
            flash('error', 'Please enter a valid name.');
        } elseif (!valid_email($email)) {
            flash('error', 'Please enter a valid email address.');
        } elseif (db_one("SELECT id FROM users WHERE email=? AND id<>?", [$email, $user_id])) {
            flash('error', 'This email is already used by another account.');
        } else {
            db_exec("UPDATE users SET name=?, email=? WHERE id=?", [$name, $email, $user_id]);
            flash('success', 'Your information has been saved.');
        }
    }

    redirect($back);
}

$user = db_one("SELECT name, email FROM users WHERE id=?", [$user_id]);
if (!$user) {
    redirect("../logout.php");
}

$stats = db_one("
    SELECT COUNT(*) AS total,
           COALESCE(SUM(status='approved'), 0) AS approved,
           COALESCE(SUM(status='pending'), 0)  AS pending
    FROM appointments
    WHERE user_id=?
", [$user_id]);

$appointments = db_all("
    SELECT a.id, s.`date`, s.`time`, a.status
    FROM appointments a
    JOIN appointment_slots s ON a.slot_id = s.id
    WHERE a.user_id=?
    ORDER BY s.`date`, s.`time`
", [$user_id]);

$slots = db_all("
    SELECT id, `date`, `time`
    FROM appointment_slots
    WHERE is_booked=0 AND TIMESTAMP(`date`, `time`) >= NOW()
    ORDER BY `date`, `time`
");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Client Dashboard</title>
<link rel="stylesheet" href="client.css">
</head>

<body>

<div class="topbar">
    <h2>Client Dashboard</h2>
    <a href="../logout.php" class="logout">Log Out</a>
</div>

<?= flash_html() ?>

<div class="stats">
    <div class="stat pending">Pending<br><b><?= (int)$stats['pending'] ?></b></div>
    <div class="stat approved">Approved<br><b><?= (int)$stats['approved'] ?></b></div>
    <div class="stat total">Total<br><b><?= (int)$stats['total'] ?></b></div>
</div>
<div class="tabs">
    <button class="tab<?= $tab === 'my' ? ' active' : '' ?>" data-target="my">My Appointments</button>
    <button class="tab<?= $tab === 'book' ? ' active' : '' ?>" data-target="book">Book Appointment</button>
    <button class="tab<?= $tab === 'profile' ? ' active' : '' ?>" data-target="profile">My Profile</button>
</div>

<div class="content">
<div id="my" class="tab-content<?= $tab === 'my' ? ' active' : '' ?>">
<div class="table-wrap">
<table>
<tr><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>
<?php foreach ($appointments as $r): ?>
<tr>
<td><?= e($r['date']) ?></td>
<td><?= e($r['time']) ?></td>
<td>
    <span class="status <?= e($r['status']) ?>-s">
        <?= e(status_label($r['status'])) ?>
    </span>
</td>
<td>
<?php if ($r['status'] !== 'cancelled'): ?>
<form method="POST">
<?= csrf_field() ?>
<button class="cancel" name="cancel" value="<?= (int)$r['id'] ?>">Cancel</button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$appointments): ?>
<tr><td colspan="4">You have no appointments yet.</td></tr>
<?php endif; ?>
</table>
</div>
</div>

<div id="book" class="tab-content<?= $tab === 'book' ? ' active' : '' ?>">
<form method="POST">
<?= csrf_field() ?>
<select name="slot_id" required>
<option value=""><?= $slots ? 'Select a slot' : 'No available slots' ?></option>
<?php foreach ($slots as $s): ?>
<option value="<?= (int)$s['id'] ?>"><?= e($s['date']) ?> - <?= e($s['time']) ?></option>
<?php endforeach; ?>
</select>
<button name="book"<?= $slots ? '' : ' disabled' ?>>Book Appointment</button>
</form>
</div>

<div id="profile" class="tab-content<?= $tab === 'profile' ? ' active' : '' ?>">
<form method="POST">
<?= csrf_field() ?>
<label>Name</label>
<input type="text" name="name" value="<?= e($user['name']) ?>" maxlength="100" required>

<label>Email</label>
<input type="email" name="email" value="<?= e($user['email']) ?>" maxlength="190" required>

<button name="save_profile">Save</button>
</form>
</div>

</div>

<script>
const tabs = document.querySelectorAll('.tab');
const contents = document.querySelectorAll('.tab-content');
tabs.forEach(t => {
    t.onclick = () => {
        tabs.forEach(x => x.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));
        t.classList.add('active');
        document.getElementById(t.dataset.target).classList.add('active');
    };
});
</script>

</body>
</html>
