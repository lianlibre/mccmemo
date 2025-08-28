<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
/**include "../includes/admin_header.php";**/
include "../includes/admin_sidebar.php";

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search = trim($_GET['search'] ?? '');
$where = "1=1";
if ($search) {
    $safe_search = $conn->real_escape_string($search);
    $where .= " AND (fullname LIKE '%$safe_search%' OR username LIKE '%$safe_search%' OR email LIKE '%$safe_search%' OR contact LIKE '%$safe_search%' OR address LIKE '%$safe_search%')";
}
$total = $conn->query("SELECT COUNT(*) FROM users WHERE $where")->fetch_row()[0];
$users = $conn->query("SELECT * FROM users WHERE $where ORDER BY fullname ASC LIMIT $per_page OFFSET $offset");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users - Admin Panel</title>
    <link rel="stylesheet" href="../includes/user_style.css">
</head>
<body>

<div class="container">
<h2>All Users</h2>
<a href="user_add.php" class="btn">+ Add User</a>
<form method="get">
    <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>
<table>
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Contact</th>
        <th>Birthday</th>
        <th>Gender</th>
        <th>Address</th>
        <th>Role</th>
        <th>Memos</th>
        <th>Actions</th>
    </tr>
    <?php while($user = $users->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($user['fullname']) ?></td>
        <td><?= htmlspecialchars($user['username'] ?? "-") ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= htmlspecialchars($user['contact'] ?? "-") ?></td>
        <td><?= htmlspecialchars($user['birthday'] ?? "-") ?></td>
        <td><?= htmlspecialchars($user['gender'] ?? "-") ?></td>
        <td><?= htmlspecialchars($user['address'] ?? "-") ?></td>
        <td><?= htmlspecialchars($user['role']) ?></td>
        <td>
            <?php
            $count = $conn->query("SELECT COUNT(*) FROM memos WHERE user_id=" . intval($user['id']))->fetch_row()[0];
            echo $count;
            ?>
            <a href="memos.php?user_id=<?= $user['id'] ?>">View</a>
        </td>
        <td>
            <a href="user_edit.php?id=<?= $user['id'] ?>">Edit</a>
            <a href="user_delete.php?id=<?= $user['id'] ?>" onclick="return confirm('Delete this user and their memos?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<!-- Pagination -->
<div>
    <?php
    $pages = ceil($total / $per_page);
    for ($i = 1; $i <= $pages; $i++) {
        if ($i == $page) echo "<strong>$i</strong> ";
        else echo "<a href='?page=$i&search=" . urlencode($search) . "'>$i</a> ";
    }
    ?>
</div>
</div>
<?php include "../includes/admin_footer.php"; ?>