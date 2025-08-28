<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

$id = intval($_GET['id']);
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
if (!$user) {
    echo "<div class='container'><h2>User not found.</h2></div>";
    include "../includes/admin_footer.php";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $role = $_POST["role"];
    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, contact=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $fullname, $email, $contact, $role, $id);
    $stmt->execute();
    $stmt->close();
    if (!empty($_POST["password"])) {
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $password, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: users.php?msg=updated");
    exit;
}


include "../includes/admin_sidebar.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users - Admin Panel</title>
    <link rel="stylesheet" href="../includes/user_style.css">
</head>
<body>
<div class="container">
<h2>Edit User</h2>
<form method="post" style="max-width:400px;">
    <label>Full Name:</label>
    <input type="text" name="fullname" required value="<?= htmlspecialchars($user['fullname']) ?>">
    <label>Email:</label>
    <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
    <label>Contact:</label>
    <input type="text" name="contact" value="<?= htmlspecialchars($user['contact']) ?>">
    <label>Password (leave blank to keep current):</label>
    <input type="password" name="password">
    <label>Role:</label>
    <select name="role" required>
        <option value="user" <?= $user['role']=='user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $user['role']=='admin' ? 'selected' : '' ?>>Admin</option>
    </select>
    <button type="submit" class="btn">Update User</button>
    <a href="users.php" class="btn secondary">Cancel</a>
</form>
</div>
<?php include "../includes/admin_footer.php"; ?>