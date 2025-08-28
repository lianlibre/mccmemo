<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

// Directory for uploaded logos/seals
$upload_dir = "../uploads/headers/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Fetch current settings (for demo, only one row: id=1)
$res = $conn->query("SELECT * FROM memo_header_settings WHERE id=1");
$settings = $res ? $res->fetch_assoc() : [];

$msg = null;

// On form submit: update or insert settings
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $header_line1 = trim($_POST["header_line1"]);
    $header_line2 = trim($_POST["header_line2"]);
    $header_title = trim($_POST["header_title"]);
    $header_school = trim($_POST["header_school"]);
    $header_address = trim($_POST["header_address"]);
    $header_office = trim($_POST["header_office"]);

    // Handle logo upload
    $header_logo = $settings['header_logo'] ?? null;
    if (isset($_FILES["header_logo"]) && $_FILES["header_logo"]["error"] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES["header_logo"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
            $header_logo = uniqid("logo_", true) . "." . $ext;
            move_uploaded_file($_FILES["header_logo"]["tmp_name"], $upload_dir . $header_logo);
        }
    }

    // Handle seal upload
    $header_seal = $settings['header_seal'] ?? null;
    if (isset($_FILES["header_seal"]) && $_FILES["header_seal"]["error"] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES["header_seal"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
            $header_seal = uniqid("seal_", true) . "." . $ext;
            move_uploaded_file($_FILES["header_seal"]["tmp_name"], $upload_dir . $header_seal);
        }
    }

    // Upsert to DB
    $stmt = $conn->prepare("REPLACE INTO memo_header_settings 
        (id, header_line1, header_line2, header_title, header_school, header_address, header_office, header_logo, header_seal) 
        VALUES (1,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $header_line1, $header_line2, $header_title, $header_school, $header_address, $header_office, $header_logo, $header_seal);
    $stmt->execute();
    $stmt->close();

    $msg = "Header settings updated!";
    // Reload updated settings
    $res = $conn->query("SELECT * FROM memo_header_settings WHERE id=1");
    $settings = $res ? $res->fetch_assoc() : [];
}

include "../includes/admin_sidebar.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Memorandum Header</title>
    <link rel="stylesheet" href="../includes/user_style.css">
</head>
<body>
<div class="container">
    <h2>Edit Memorandum Header</h2>
    <?php if ($msg): ?>
        <div style="color:green;"><b><?= htmlspecialchars($msg) ?></b></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" style="max-width:500px;">
        <fieldset style="margin:16px 0; padding:12px; border:1px solid #ddd;">
            <legend>Memorandum Header Center</legend>
            <label for="header_line1">Top Line 1:</label>
            <input type="text" id="header_line1" name="header_line1" value="<?= htmlspecialchars($settings['header_line1'] ?? 'Republic of the Philippines') ?>">

            <label for="header_line2">Top Line 2:</label>
            <input type="text" id="header_line2" name="header_line2" value="<?= htmlspecialchars($settings['header_line2'] ?? 'Region VII, Central Visayas') ?>">

            <label for="header_title">Title:</label>
            <input type="text" id="header_title" name="header_title" value="<?= htmlspecialchars($settings['header_title'] ?? 'Municipality of Madridejos') ?>">

            <label for="header_school">School/Office Name:</label>
            <input type="text" id="header_school" name="header_school" value="<?= htmlspecialchars($settings['header_school'] ?? 'MADRIDEJOS COMMUNITY COLLEGE') ?>">

            <label for="header_address">Address:</label>
            <input type="text" id="header_address" name="header_address" value="<?= htmlspecialchars($settings['header_address'] ?? 'Crossing Bunakan, Madridejos, Cebu') ?>">

            <label for="header_office">Office:</label>
            <input type="text" id="header_office" name="header_office" value="<?= htmlspecialchars($settings['header_office'] ?? 'OFFICE OF THE COLLEGE PRESIDENT') ?>">
        </fieldset>

        <fieldset style="margin:16px 0; padding:12px; border:1px solid #ddd;">
            <legend>Logos/Seals</legend>
            <label for="header_logo">School Logo (left):</label>
            <input type="file" id="header_logo" name="header_logo" accept="image/*">
            <div style="margin-bottom:10px;">
                <img id="logoPreview" src="<?= isset($settings['header_logo']) ? $upload_dir . $settings['header_logo'] : '../path/to/mcc_logo.png' ?>" alt="Logo Preview" style="display:block;max-width:100px;max-height:100px;background:#eee;">
            </div>
            <label for="header_seal">Municipality Seal (right):</label>
            <input type="file" id="header_seal" name="header_seal" accept="image/*">
            <div>
                <img id="sealPreview" src="<?= isset($settings['header_seal']) ? $upload_dir . $settings['header_seal'] : '../path/to/madridejos_seal.png' ?>" alt="Seal Preview" style="display:block;max-width:90px;max-height:90px;background:#eee;">
            </div>
        </fieldset>

        <button type="submit" class="btn">Save Header Settings</button>
        <a href="dashboard.php" class="btn secondary">Cancel</a>
    </form>
</div>
<script>
// Optional: JS live preview for logo/seal
document.getElementById('header_logo').addEventListener('change', function(event){
    const [file] = event.target.files;
    if (file) {
        document.getElementById('logoPreview').src = URL.createObjectURL(file);
    }
});
document.getElementById('header_seal').addEventListener('change', function(event){
    const [file] = event.target.files;
    if (file) {
        document.getElementById('sealPreview').src = URL.createObjectURL(file);
    }
});
</script>
<?php include "../includes/admin_footer.php"; ?>
</body>
</html>