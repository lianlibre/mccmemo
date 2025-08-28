<?php
session_start();
require_once "includes/db.php";
$error = "";
$email_error = "";
$success = false;

// Sample departments array (you can fetch from DB instead)
$departments = [
    "Office of the College President",
    "Office of the Registrar",
    "Office of the Student Affairs",
    "Faculty",
    "Finance Office",
    "Library",
    "BSIT Department",
    "HM Department",
    "BEED Department",
    "BSBA Department",
    "Guidance Office",
    "Other"
];

// If you want to fetch from DB instead, use:
// $departments = [];
// $result = $conn->query("SELECT name FROM departments ORDER BY name ASC");
// while ($row = $result->fetch_assoc()) {
//     $departments[] = $row['name'];
// }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $birthday = trim($_POST["birthday"]);
    $gender = trim($_POST["gender"]);
    $address = trim($_POST["address"]);
    $department = trim($_POST["department"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];
    // Prefer MS365 or @mcclawis.edu.ph email
    if (
        stripos($email, "@mcclawis.edu.ph") === false &&
        stripos($email, "@outlook.com") === false &&
        stripos($email, "@office365.com") === false &&
        stripos($email, "@microsoft.com") === false
    ) {
        $email_error = "Please use your MS365 or @mcclawis.edu.ph email for registration.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email or username already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = "user";
            $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, contact, birthday, gender, address, department, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $fullname, $username, $email, $contact, $birthday, $gender, $address, $department, $hash, $role);
            $stmt->execute();
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['role'] = $role;
            $_SESSION['fullname'] = $fullname;
            $success = true;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - MemoGen</title>
    <link rel="stylesheet" href="includes/user_style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container" style="max-width:410px;margin-top:80px;">
    <h2>Register</h2>
    <form method="post" id="registerForm">
        <label>First Name:</label>
        <input type="text" name="fullname" required>
        <label>Last Name:</label>
        <input type="text" name="username" required>
        <label>Email: <span style="color:#1976d2"></span></label>
        <input type="email" name="email" required>
        <label>Contact:</label>
        <input type="text" name="contact">
        <label>Birthday:</label>
        <input type="date" name="birthday">
        <label>Gender:</label>
        <select name="gender">
            <option value="">Select...</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <label>Address:</label>
        <input type="text" name="address">
        <label>Department/Organization:</label>
        <select name="department" required>
            <option value="">Select...</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Password:</label>
        <input type="password" name="password" required>
        <label>Confirm Password:</label>
        <input type="password" name="confirm" required>
        <button type="submit" class="btn">Register</button>
        <a href="login.php" class="btn secondary" style="margin-left:10px;">Login</a>
    </form>
</div>
<script>
<?php if ($error): ?>
Swal.fire({
    icon: 'error',
    title: 'Registration Failed',
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
<?php if ($success): ?>
Swal.fire({
    icon: 'success',
    title: 'Registration Successful!',
    text: 'Welcome to MemoGen! You will now be redirected to your dashboard.',
    confirmButtonColor: '#1976d2'
}).then(function() {
    window.location.href = "user/dashboard.php";
});
<?php endif; ?>
</script>
</body>
</html>