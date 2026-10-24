<?php
require_once __DIR__ . "/db.php";

$base = $base ?? '../';

if (empty($_SESSION['user_id'])) {
    redirect($base . "login.php");
}

if (isset($required_role) && ($_SESSION['role'] ?? '') !== $required_role) {
    redirect($base . home_for_role($_SESSION['role'] ?? ''));
}
