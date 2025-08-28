<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
include "../includes/admin_sidebar.php";

$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_admins = $conn->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];
$total_memos = $conn->query("SELECT COUNT(*) FROM memos")->fetch_row()[0];
$total_templates = $conn->query("SELECT COUNT(*) FROM memo_templates")->fetch_row()[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users - Admin Panel</title>
    <link rel="stylesheet" href="../includes/user_style.css">
</head>
<body>
<div class="container">
    <h2>Admin Dashboard</h2>
    <div class="dashboard-metrics" style="display:flex;gap:28px;margin-bottom:36px;flex-wrap:wrap;">
        <div class="metric-card">
            <h2><?= $total_users ?></h2>
            <span>Users</span>
        </div>
        <!--<div class="metric-card">
            <h2><?= $total_admins ?></h2>
            <span>Admins</span>
        </div>-->
        <div class="metric-card">
            <h2><?= $total_memos ?></h2>
            <span>Memorandums</span>
        </div>
    </div>
</div>
<?php include "../includes/admin_footer.php"; ?>