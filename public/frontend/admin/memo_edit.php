<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

// Fetch memo
$id = intval($_GET['id']);
$memo = $conn->query("SELECT * FROM memos WHERE id=$id")->fetch_assoc();
if (!$memo) {
    echo "<div class='container'><h2>Memo not found.</h2></div>";
    include "../includes/admin_footer.php";
    exit;
}

// Fetch templates
$templates = $conn->query("SELECT * FROM memo_templates ORDER BY subject ASC")->fetch_all(MYSQLI_ASSOC);
// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY fullname ASC")->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = intval($_POST["user_id"]);
    $to = trim($_POST["to"]);
    $from = trim($_POST["from"]);
    $subject = trim($_POST["subject"]);
    $body = trim($_POST["body"]);
    $stmt = $conn->prepare("UPDATE memos SET user_id=?, `to`=?, `from`=?, subject=?, body=? WHERE id=?");
    $stmt->bind_param("issssi", $user_id, $to, $from, $subject, $body, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: memos.php?msg=updated");
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
<h2>Edit Memorandum</h2>
<form method="post" autocomplete="off" style="max-width:480px;margin:0 auto;">
    <label for="user_id">User:</label>
    <select id="user_id" name="user_id" required>
        <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $u['id']==$memo['user_id']?'selected':'' ?>>
                <?= htmlspecialchars($u['fullname']) ?> (<?= htmlspecialchars($u['email']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <label for="to">To:</label>
    <input type="text" id="to" name="to" required value="<?= htmlspecialchars($memo['to']) ?>">
    <label for="from">From:</label>
    <input type="text" id="from" name="from" required value="<?= htmlspecialchars($memo['from']) ?>">
    <label for="subject">Subject:</label>
    <select id="subject" name="subject" required onchange="fillBodyFromTemplate()">
        <?php foreach ($templates as $tpl): ?>
            <option value="<?= htmlspecialchars($tpl['subject']) ?>" data-body="<?= htmlspecialchars($tpl['body']) ?>"
                <?= $tpl['subject']==$memo['subject']?'selected':'' ?>>
                <?= htmlspecialchars($tpl['subject']) ?>
            </option>
        <?php endforeach; ?>
        <option value="<?= htmlspecialchars($memo['subject']) ?>"
                <?= !in_array($memo['subject'], array_column($templates, 'subject')) ? 'selected' : '' ?>>
            <?= htmlspecialchars($memo['subject']) ?> (Custom)
        </option>
    </select>
    <label for="body">Body:</label>
    <textarea id="body" name="body" rows="6" required><?= htmlspecialchars($memo['body']) ?></textarea>
    <button type="submit" class="btn">Update Memorandum</button>
    <a href="memos.php" class="btn secondary" style="margin-left:10px;">Cancel</a>
</form>
</div>
<script>
function fillBodyFromTemplate() {
    var subjectSelect = document.getElementById('subject');
    var selected = subjectSelect.options[subjectSelect.selectedIndex];
    var body = selected.getAttribute('data-body') || '';
    document.getElementById('body').value = body;
}
</script>
<?php include "../includes/admin_footer.php"; ?>