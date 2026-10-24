<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect($url)
{
    header("Location: $url");
    exit();
}

function home_for_role($role)
{
    return $role === 'manager' ? 'manager/dashboard.php' : 'client/dashboard.php';
}

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid()
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

function flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_html()
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert ' . e($f['type']) . '">' . e($f['message']) . '</div>';
}

function status_label($status)
{
    $labels = [
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'cancelled' => 'Cancelled',
    ];
    return $labels[$status] ?? $status;
}

function valid_name($name)
{
    return preg_match('/^.{2,100}$/su', $name) === 1;
}

function valid_email($email)
{
    return strlen($email) <= 190 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_phone($phone)
{
    return preg_match('/^[0-9+\-\s()]{6,20}$/', $phone) === 1;
}

function db_run($sql, $params = [])
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($params) {
        $types = '';
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : 's';
        }
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function db_all($sql, $params = [])
{
    return db_run($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function db_one($sql, $params = [])
{
    $row = db_run($sql, $params)->get_result()->fetch_assoc();
    return $row ?: null;
}

function db_value($sql, $params = [])
{
    $row = db_run($sql, $params)->get_result()->fetch_row();
    return $row ? $row[0] : null;
}

function db_exec($sql, $params = [])
{
    return db_run($sql, $params)->affected_rows;
}

function approve_appointment($id)
{
    return db_exec(
        "UPDATE appointments SET status='approved' WHERE id=? AND status='pending'",
        [(int)$id]
    ) === 1;
}

function cancel_appointment($id, $user_id = null, $pending_only = false)
{
    global $conn;
    $condition = $pending_only ? "status='pending'" : "status<>'cancelled'";
    $sql = "SELECT slot_id FROM appointments WHERE id=? AND $condition";
    $params = [(int)$id];
    if ($user_id !== null) {
        $sql .= " AND user_id=?";
        $params[] = (int)$user_id;
    }
    $sql .= " FOR UPDATE";

    $conn->begin_transaction();
    try {
        $row = db_one($sql, $params);
        if (!$row) {
            $conn->rollback();
            return false;
        }
        db_exec("UPDATE appointments SET status='cancelled' WHERE id=?", [(int)$id]);
        db_exec("UPDATE appointment_slots SET is_booked=0 WHERE id=?", [(int)$row['slot_id']]);
        $conn->commit();
        return true;
    } catch (Throwable $t) {
        $conn->rollback();
        throw $t;
    }
}

function book_slot($user_id, $slot_id)
{
    global $conn;
    $conn->begin_transaction();
    try {
        $taken = db_exec(
            "UPDATE appointment_slots SET is_booked=1
             WHERE id=? AND is_booked=0 AND TIMESTAMP(`date`, `time`) >= NOW()",
            [(int)$slot_id]
        ) === 1;
        if (!$taken) {
            $conn->rollback();
            return false;
        }
        db_exec(
            "INSERT INTO appointments (user_id, slot_id, status) VALUES (?, ?, 'pending')",
            [(int)$user_id, (int)$slot_id]
        );
        $conn->commit();
        return true;
    } catch (Throwable $t) {
        $conn->rollback();
        throw $t;
    }
}

function handle_manager_action($back)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !(isset($_POST['approve']) || isset($_POST['cancel']))) {
        return;
    }
    if (!csrf_valid()) {
        flash('error', 'Invalid request. Please try again.');
        redirect($back);
    }
    if (isset($_POST['approve'])) {
        if (approve_appointment($_POST['approve'])) {
            flash('success', 'Appointment approved.');
        } else {
            flash('error', 'This request can no longer be approved.');
        }
    } else {
        if (cancel_appointment($_POST['cancel'], null, true)) {
            flash('success', 'Appointment rejected.');
        } else {
            flash('error', 'This request can no longer be rejected.');
        }
    }
    redirect($back);
}
