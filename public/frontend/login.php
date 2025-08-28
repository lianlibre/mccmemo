<?php
session_start();
require_once "includes/db.php";
$error = "";
$email_error = "";
$login_success = false;
$redirect_url = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    // Prefer MS365 or @mcclawis.edu.ph email
    if (
        stripos($email, "@mcclawis.edu.ph") === false &&
        stripos($email, "@outlook.com") === false &&
        stripos($email, "@office365.com") === false &&
        stripos($email, "@microsoft.com") === false
    ) {
        $email_error = "Please use your MS365 or @mcclawis.edu.ph email for login.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];
            $login_success = true;
            if ($user['role'] === 'admin') {
                $_SESSION['admin_name'] = $user['fullname'];
                $redirect_url = "admin/dashboard.php";
            } else {
                $redirect_url = "user/dashboard.php";
            }
            // Do not redirect immediately: show SweetAlert first
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - MemoGen</title>
    <link rel="stylesheet" href="includes/user_style.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container" style="max-width:410px;margin-top:80px;">
    <h2>Login</h2>
    <form method="post" id="loginForm">
        <label>Email: <span style="color:#1976d2">(MS365 or @mcclawis.edu.ph)</span></label>
        <input type="email" name="email" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn">Login</button>
        <a href="register.php" class="btn secondary" style="margin-left:10px;">Register</a>
    </form>
</div>
<script>
<?php if ($error): ?>
Swal.fire({
    icon: 'error',
    title: 'Login Failed',
    text: <?= json_encode($error) ?>,
    confirmButtonColor: '#1976d2'
});
<?php endif; ?>
<?php if ($email_error): ?>
Swal.fire({
    icon: 'warning',
    title: 'Email Required',
    text: <?= json_encode($email_error) ?>,
    confirmButtonColor: '#1976d2'
});
<?php endif; ?>
<?php if ($login_success): ?>
Swal.fire({
    icon: 'success',
    title: 'Login Successful!',
    text: 'Welcome to MemoGen!',
    confirmButtonColor: '#1976d2'
}).then(function() {
    window.location.href = <?= json_encode($redirect_url) ?>;
});
<?php endif; ?>
</script>
</body>
</html>