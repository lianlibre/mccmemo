<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, contact, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $contact, $password, $role);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php?msg=added");
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
<h2>Add User</h2>
<form method="post" style="max-width:400px;">
    <label>Full Name:</label>
    <input type="text" name="fullname" required>
    <label>Email:</label>
    <input type="email" name="email" required>
    <label>Contact:</label>
    <input type="text" name="contact">
    <label>Password:</label>
    <input type="password" name="password" required>
    <label>Role:</label>
    <select name="role" required>
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>
    <button type="submit" class="btn">Add User</button>
    <a href="users.php" class="btn secondary">Cancel</a>
</form>
</div>
<?php include "../includes/admin_footer.php"; ?>