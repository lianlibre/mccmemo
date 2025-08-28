<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

// Archive logic
if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
    $archive_id = intval($_GET['archive']);
    $conn->query("UPDATE memos SET archived = 1 WHERE id = $archive_id");
    header("Location: archived_memos.php?msg=archived");
    exit;
}

// Pagination for active memos
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$total = $conn->query("SELECT COUNT(*) FROM memos WHERE archived=0")->fetch_row()[0];

// Note: Make sure your users table has the 'depaertment' field
$memos = $conn->query("SELECT m.*, u.department FROM memos m JOIN users u ON m.user_id=u.id WHERE m.archived=0 ORDER BY m.created_at DESC LIMIT $per_page OFFSET $offset");

include "../includes/admin_sidebar.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Active Memorandums - Admin Panel</title>
    <link rel="stylesheet" href="../includes/user_style.css">
    <style>
    .archive-link {
        color: #5673ddff;
        margin-left: 8px;
        text-decoration: none;
        font-weight: bold;
        cursor: pointer;
    }
    .archive-link:hover {
        color: #2a7de2ff;
        text-decoration: underline;
    }
    </style>
</head>
<body>
<div class="container">
<h2>Active Memorandums</h2>
<a href="archived_memos.php" class="btn">Show Archived</a>
<a href="memo_add.php" class="btn">+ Add Memorandum</a>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'archived'): ?>
    <div style="color: #6492fcff;"><b>Memorandum archived!</b></div>
<?php endif; ?>
<table>
    <tr>
        <th>No.</th>
        <th>Subject</th>
        <th>Body</th>
        <th>To</th>
        <th>From</th> <!-- Now shows department -->
        <th>Date</th>
        <th>Actions</th>
    </tr>
    <?php
    $row_num = $offset + 1;
    while($memo = $memos->fetch_assoc()):
    ?>
    <tr>
        <td><?= $row_num ?></td>
        <td><?= htmlspecialchars($memo['subject']) ?></td>
        <td><?= htmlspecialchars(mb_strimwidth($memo['body'], 0, 70, "...")) ?></td>
        <td><?= isset($memo['to']) ? htmlspecialchars($memo['to']) : '-' ?></td>
        <td><?= htmlspecialchars($memo['department']) ?></td>
        <td><?= htmlspecialchars($memo['created_at']) ?></td>
        <td>
            <a href="memo_edit.php?id=<?= $memo['id'] ?>">Edit</a>
            <a href="memo_message.php?id=<?= $memo['id'] ?>">Message</a>
            <a href="memos.php?archive=<?= $memo['id'] ?>" class="archive-link" onclick="return confirm('Archive this memorandum?')">Archive</a>
        </td>
    </tr>
    <?php
    $row_num++;
    endwhile;
    ?>
</table>
<!-- Pagination -->
<div>
    <?php
    $pages = ceil($total / $per_page);
    for ($i = 1; $i <= $pages; $i++) {
        $url = "admin_memos.php?page=$i";
        if ($i == $page) echo "<strong>$i</strong> ";
        else echo "<a href='$url'>$i</a> ";
    }
    ?>
</div>
</div>
<?php include "../includes/admin_footer.php"; ?>
</body>
</html>