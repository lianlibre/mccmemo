<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

// Directory for uploaded logos/seals
$upload_dir = "../uploads/headers/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Department/Organization list (for TO field)
$departments = [
    "Office of the College President",
    "Office of the Registrar",
    "Office of the Student Affairs",
    "Faculty",
    "Finance Office",
    "Library",
    "BSIT Department",
    "BSBA Department",
    "BEED Department",
    "HM Department",
    "Guidance Office"
];

// Fetch default header settings
$res = $conn->query("SELECT * FROM memo_header_settings WHERE id=1");
$header_settings = $res ? $res->fetch_assoc() : [];
$header_line1   = $header_settings['header_line1']   ?? 'Republic of the Philippines';
$header_line2   = $header_settings['header_line2']   ?? 'Region VII, Central Visayas';
$header_title   = $header_settings['header_title']   ?? 'Municipality of Madridejos';
$header_school  = $header_settings['header_school']  ?? 'MADRIDEJOS COMMUNITY COLLEGE';
$header_address = $header_settings['header_address'] ?? 'Crossing Bunakan, Madridejos, Cebu';
$header_office  = $header_settings['header_office']  ?? 'OFFICE OF THE COLLEGE PRESIDENT';
$header_logo    = $header_settings['header_logo']    ?? 'default_logo.png';
$header_seal    = $header_settings['header_seal']    ?? 'default_seal.png';

// For preview: get next memo number
$preview_result = $conn->query("SELECT MAX(memo_number) AS max_num FROM memos");
$preview_row = $preview_result->fetch_assoc();
$preview_memo_number = $preview_row['max_num'] ? $preview_row['max_num'] + 1 : 1;
$preview_memo_number_str = sprintf('%03d', $preview_memo_number);

// Get logged-in user department (for FROM field)
$user_id = $_SESSION['user_id'] ?? null;
$user_department = "";
if ($user_id) {
    $stmt = $conn->prepare("SELECT department FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_department);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $to = $_POST["to"] ?? [];
    $to = array_unique(array_filter(array_map('trim', $to)));

    $from = $user_department; // fixed department of logged-in admin
    $subject = trim($_POST["subject"]);
    $body = trim($_POST["body"]);
    $header_line1 = trim($_POST["header_line1"]);
    $header_line2 = trim($_POST["header_line2"]);
    $header_title = trim($_POST["header_title"]);
    $header_school = trim($_POST["header_school"]);
    $header_address = trim($_POST["header_address"]);
    $header_office = trim($_POST["header_office"]);
    $signed_by = trim($_POST["signed_by"]);
    $sign_position = trim($_POST["sign_position"]);
    $sign_org = trim($_POST["sign_org"]);

    // Handle logo upload
    $header_logo = $header_settings['header_logo'] ?? null;
    if (isset($_FILES["header_logo"]) && $_FILES["header_logo"]["error"] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES["header_logo"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
            $header_logo = uniqid("logo_", true) . "." . $ext;
            move_uploaded_file($_FILES["header_logo"]["tmp_name"], $upload_dir . $header_logo);
        }
    }

    // Handle seal upload
    $header_seal = $header_settings['header_seal'] ?? null;
    if (isset($_FILES["header_seal"]) && $_FILES["header_seal"]["error"] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES["header_seal"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
            $header_seal = uniqid("seal_", true) . "." . $ext;
            move_uploaded_file($_FILES["header_seal"]["tmp_name"], $upload_dir . $header_seal);
        }
    }

    // Get next memo number
    $result = $conn->query("SELECT MAX(memo_number) AS max_num FROM memos");
    $row = $result->fetch_assoc();
    $next_memo_number = $row['max_num'] ? $row['max_num'] + 1 : 1;

    // Insert memo
    $stmt = $conn->prepare("
        INSERT INTO memos 
        (memo_number, `from`, subject, body, header_line1, header_line2, header_title, header_school, header_address, header_office, header_logo, header_seal, signed_by, sign_position, sign_org, user_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "isssssssssssssi",
        $next_memo_number, $from, $subject, $body,
        $header_line1, $header_line2, $header_title, $header_school, $header_address, $header_office,
        $header_logo, $header_seal, $signed_by, $sign_position, $sign_org, $user_id
    );
    $stmt->execute();
    $memo_id = $conn->insert_id;
    $stmt->close();

    // Save recipients
    foreach ($to as $dept) {
        $dept = htmlspecialchars(trim($dept), ENT_QUOTES, 'UTF-8');
        $stmt2 = $conn->prepare("INSERT INTO memo_recipients (memo_id, department) VALUES (?, ?)");
        $stmt2->bind_param("is", $memo_id, $dept);
        $stmt2->execute();
        $stmt2->close();
    }

    // Log activity
    $action = "Created memo";
    $details = "Subject: $subject";
    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, memo_id, details) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("isis", $user_id, $action, $memo_id, $details);
    $log_stmt->execute();
    $log_stmt->close();

    $memo_number_str = sprintf('%03d', $next_memo_number);

    header("Location: memos.php?msg=added&memo_number=" . $memo_number_str);
    exit;
}

include "../includes/admin_sidebar.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Memorandum - Admin Panel</title>
    <link rel="stylesheet" href="../includes/user_style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { background: #f5f5f5; }
        .memo-main { width: 760px; margin: 40px auto; background: #fff; padding: 60px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .memo-header { text-align: center; margin-bottom: 18px; position: relative; }
        .memo-header-logo { position: absolute; left: 0; top: 0; width: 120px; height: 120px; }
        .memo-header-seal { position: absolute; right: 0; top: 0; width: 120px; height: 120px; }
        .memo-header-center { margin-left: 140px; margin-right: 140px; }
        .memo-title { font-size: 1.09rem; font-weight: bold; margin-top: 30px; margin-bottom: 10px; }
        .memo-number-date { font-size: 1.02rem; margin-bottom: 6px; }
        .memo-meta { margin-top: 22px; margin-bottom: 14px; font-size: 1.07rem; }
        .memo-meta label { font-weight: bold; min-width: 60px; display: inline-block; }
        .memo-subject { font-weight: bold; margin-top: 16px; margin-bottom: 12px; font-size: 1.09rem; }
        .memo-body { margin-bottom: 24px; font-size: 1.05rem; line-height: 1.65; white-space: pre-line; }
        .memo-signature { margin-top: 54px; min-height: 60px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<div class="container">
<h2>Add Memorandum</h2>
<form method="post" enctype="multipart/form-data" autocomplete="off" style="max-width:480px;margin:0 auto;" oninput="memoPreviewUpdate()">
    <label for="to">To (Department/Organization):</label>
    <select id="to" name="to[]" multiple="multiple" style="width:100%;" required>
        <?php foreach ($departments as $dept): ?>
            <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="from">From:</label>
    <input type="text" id="from" name="from" value="<?= htmlspecialchars($user_department) ?>" readonly>

    <label for="subject">Subject:</label>
    <input type="text" id="subject" name="subject" required oninput="memoPreviewUpdate()">
    <label for="body">Body:</label>
    <textarea id="body" name="body" rows="6" required oninput="memoPreviewUpdate()"></textarea>

    <!-- header + logos inputs remain unchanged -->

    <label for="signed_by">Signed By:</label>
    <input type="text" id="signed_by" name="signed_by" required oninput="memoPreviewUpdate()">
    <label for="sign_position">Position:</label>
    <input type="text" id="sign_position" name="sign_position" required oninput="memoPreviewUpdate()">
    <label for="sign_org">Signature Dept/Org:</label>
    <input type="text" id="sign_org" name="sign_org" required oninput="memoPreviewUpdate()">

    <button type="submit" class="btn">Add Memorandum</button>
    <a href="memos.php" class="btn secondary" style="margin-left:10px;">Cancel</a>
</form>

<!-- Memo Preview -->
<div class="memo-main no-print" style="margin-top:40px;">
    <div class="memo-title">MEMORANDUM ORDER</div>
    <div class="memo-number-date">
        <strong>NO. 2025 – <span id="memoNumberPreview"><?= $preview_memo_number_str ?></span></strong><br>
        <span id="datePreview"><?= date('F j, Y') ?></span>
    </div>
    <div class="memo-meta">
        <div><label>TO:</label> <span class="meta-value" id="toPreview"></span></div>
        <div><label>FROM:</label> <span class="meta-value"><?= htmlspecialchars($user_department) ?></span></div>
    </div>
    <div class="memo-subject"><label>SUBJECT:</label> <span id="subjectPreview"></span></div>
    <div class="memo-body" id="bodyPreview"></div>
    <div class="memo-signature">
        <strong id="signByPreview"></strong><br>
        <span id="signPositionPreview"></span><br>
        <span id="signOrgPreview"></span>
    </div>
</div>
</div>
<script>
$(document).ready(function() {
    $('#to').select2({ placeholder: "Select or type department(s)...", tags: true });
    $('#to').on('change', memoPreviewUpdate);
});
function memoPreviewUpdate() {
    var selected = $('#to').val() || [];
    document.getElementById('toPreview').innerText = selected.join(", ");
    document.getElementById('subjectPreview').innerText = document.getElementById('subject').value;
    var body = document.getElementById('body').value;
    document.getElementById('bodyPreview').innerHTML = body.replace(/\n/g, "<br>");
    document.getElementById('signByPreview').innerText = document.getElementById('signed_by').value;
    document.getElementById('signPositionPreview').innerText = document.getElementById('sign_position').value;
    document.getElementById('signOrgPreview').innerText = document.getElementById('sign_org').value;
}
window.onload = memoPreviewUpdate;
</script>

<?php include "../includes/admin_footer.php"; ?>
</body>
</html>