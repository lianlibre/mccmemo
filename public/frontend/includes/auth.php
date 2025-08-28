<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /memo_gen/login.php");
    exit;
}
if (isset($require_admin) && $require_admin && $_SESSION['role'] !== 'admin') {
    header("Location: /memo_gen/user/dashboard.php");
    exit;
}
if (isset($require_user) && $require_user && $_SESSION['role'] !== 'user') {
    header("Location: /memo_gen/admin/dashboard.php");
    exit;
}
?>