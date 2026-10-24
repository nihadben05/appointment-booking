<?php
require_once "includes/db.php";

if (!empty($_SESSION['user_id'])) {
    redirect(home_for_role($_SESSION['role'] ?? ''));
}

$error = '';

if (isset($_POST['login'])) {
    if (!csrf_valid()) {
        $error = "Invalid request. Please try again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $user = db_one("SELECT id, role, password FROM users WHERE email=?", [$email]);

        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role']    = $user['role'];
            redirect(home_for_role($user['role']));
        }

        $error = "Invalid email or password";
    }
}

$title = "Log In";
include "includes/header.php";
?>

<div class="login-container">
    <h2>Log In</h2>

    <?= flash_html() ?>
    <?php if ($error !== ''): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?= csrf_field() ?>
        <input type="email" name="email" placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Log in</button>
    </form>

    <p>Don't have an account? <a href="register.php">Create a new account</a></p>
</div>

<?php include "includes/footer.php"; ?>
