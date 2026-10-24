<?php
require_once "includes/db.php";

if (!empty($_SESSION['user_id'])) {
    redirect(home_for_role($_SESSION['role'] ?? ''));
}

$error = '';
$name = $phone = $age = $email = '';

if (isset($_POST['register'])) {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age   = trim($_POST['age'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!csrf_valid()) {
        $error = "Invalid request. Please try again.";
    } elseif (!valid_name($name)) {
        $error = "Please enter a valid name.";
    } elseif (!valid_phone($phone)) {
        $error = "Please enter a valid phone number.";
    } elseif (!ctype_digit($age) || (int)$age < 1 || (int)$age > 120) {
        $error = "Please enter a valid age.";
    } elseif (!valid_email($email)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($pass) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif (db_one("SELECT id FROM users WHERE email=?", [$email])) {
        $error = "This email is already registered.";
    } else {
        db_exec(
            "INSERT INTO users (role, name, phone, age, email, password)
             VALUES ('client', ?, ?, ?, ?, ?)",
            [$name, $phone, (int)$age, $email, password_hash($pass, PASSWORD_DEFAULT)]
        );
        flash('success', 'Account created successfully. Please log in.');
        redirect("login.php");
    }
}

$title = "Create a New Account";
include "includes/header.php";
?>

<div class="login-container">
    <h2>Create a New Account</h2>

    <?php if ($error !== ''): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?= csrf_field() ?>
        <input type="text" name="name" placeholder="Name" value="<?= e($name) ?>" maxlength="100" required>
        <input type="text" name="phone" placeholder="Phone number" value="<?= e($phone) ?>" maxlength="20" required>
        <input type="number" name="age" placeholder="Age" value="<?= e($age) ?>" min="1" max="120" required>
        <input type="email" name="email" placeholder="Email" value="<?= e($email) ?>" maxlength="190" required>
        <input type="password" name="password" placeholder="Password (min. 8 characters)" minlength="8" required>
        <button name="register">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Log in</a></p>
</div>

<?php include "includes/footer.php"; ?>
