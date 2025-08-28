<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $birthday = trim($_POST["birthday"]);
    $gender = trim($_POST["gender"]);
    $address = trim($_POST["address"]);
    $department = trim($_POST["department"]); // Added department/organization

    $stmt = $conn->prepare("UPDATE users SET fullname=?, username=?, email=?, contact=?, birthday=?, gender=?, address=?, department=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $fullname, $username, $email, $contact, $birthday, $gender, $address, $department, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: profile.php?msg=updated");
    exit;
}

include "../includes/user_sidebar.php";
?>
<div class="container">
    <h2>My Profile</h2>
    <?php if (isset($_GET['msg'])): ?>
        <div style="color:green;">Profile updated!</div>
    <?php endif; ?>
    <form method="post" style="max-width:420px;">
        <label>First Name:</label>
        <input type="text" name="fullname" required value="<?= htmlspecialchars($user['fullname']) ?>">
        <label>Last Name:</label>
        <input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>">
        <label>Email:</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
        <label>Contact:</label>
        <input type="text" name="contact" value="<?= htmlspecialchars($user['contact']) ?>">
        <label>Birthday:</label>
        <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday']) ?>">
        <label>Gender:</label>
        <select name="gender">
            <option value="">Select...</option>
            <option value="Male" <?= $user['gender'] == "Male" ? "selected" : "" ?>>Male</option>
            <option value="Female" <?= $user['gender'] == "Female" ? "selected" : "" ?>>Female</option>
            <option value="Other" <?= $user['gender'] == "Other" ? "selected" : "" ?>>Other</option>
        </select>
        <label>Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>">
        <label>Department/Organization:</label>
        <select name="department" required>
            <option value="">Select...</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?= htmlspecialchars($dept) ?>"
                    <?= ($user['department'] == $dept ? "selected" : "") ?>>
                    <?= htmlspecialchars($dept) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>
<?php include "../includes/user_footer.php"; ?>