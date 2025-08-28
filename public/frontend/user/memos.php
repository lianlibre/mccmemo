<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$user_department = $user['department']; // User's department/organization
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Memorandums</title>
    <link rel="stylesheet" href="../includes/user_style.css">
    <style>
        /* --- Styles --- */
        .search-bar { margin-bottom: 18px; text-align: right; }
        .search-bar input[type="text"] { padding: 7px 13px; border-radius: 4px; border: 1px solid #aaa; font-size: 1.04rem; width: 260px; max-width: 100%; margin-right: 7px; }
        .search-bar button { background: #1976d2; color: #fff; border: none; padding: 7px 19px; border-radius: 4px; cursor: pointer; font-size: 1.04rem; font-weight: bold; }
        .search-bar button:hover { background: #1259a7; }
        .menu-dropdown { position: relative; display: inline-block; }
        .menu-btn { background: #1976d2; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1rem; }
        .menu-content { display: none; position: absolute; z-index: 10; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.12); min-width: 120px; right: 0; border-radius: 5px; margin-top: 6px; }
        .menu-content button { display: block; padding: 8px 16px; color: #222; background: none; border: none; width: 100%; text-align: left; cursor: pointer; font-size: 0.95rem; }
        .menu-content button:hover { background: #f0f0f0; }
        .archive-link, .retrieve-link { color: #1976d2; margin-left: 8px; text-decoration: none; font-weight: bold; cursor: pointer; font-size: 1rem; padding: 4px 12px; border-radius: 3px; }
        .archive-link:hover, .retrieve-link:hover { text-decoration: underline; background: #e3e8fc; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f0f0f0; }
        #new-memo-notification { display: none; background: #1976d2; color: #fff; padding: 18px; margin-bottom: 14px; font-weight: bold; position: fixed; top: 22px; right: 22px; z-index: 9999; border-radius: 7px; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        #new-memo-notification .close-btn { background: none; color: #fff; border: none; font-size: 1.1rem; float: right; margin-left: 16px; cursor: pointer; }
    </style>
</head>
<body>
<?php include "../includes/user_sidebar.php"; ?>
<div class="container">
    <h2>My Memorandums</h2>
    <div id="new-memo-notification">
        <span id="new-memo-text"></span>
        <button class="close-btn" onclick="document.getElementById('new-memo-notification').style.display='none'">&times;</button>
    </div>
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search subject, body, from, to...">
        <button type="button" onclick="ajaxSearchMemos()">Search</button>
        <button type="button" onclick="clearSearch()">Clear</button>
    </div>
    <div style="margin-bottom:18px;">
        <a href="memos.php" class="btn">Show Active</a>
        <a href="memos.php?archived=1" class="btn">Show Archived</a>
    </div>
    <div id="memoTable">
        <!-- Memo table will be loaded here by AJAX -->
    </div>
</div>

<script>
// Toggle menu dropdown
function toggleMenu(btn) {
    document.querySelectorAll('.menu-content').forEach(function(menu){
        if(menu !== btn.nextElementSibling) menu.style.display = 'none';
    });
    var menu = btn.nextElementSibling;
    menu.style.display = (menu.style.display === 'block' ? 'none' : 'block');
}
document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('menu-btn')) {
        document.querySelectorAll('.menu-content').forEach(function(menu){
            menu.style.display = 'none';
        });
    }
});

// AJAX search
function ajaxSearchMemos(page = 1, archived = 0) {
    var search = document.getElementById('searchInput').value;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'ajax_memo_search.php?search=' + encodeURIComponent(search) + '&page=' + page + '&archived=' + archived, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('memoTable').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
function clearSearch() {
    document.getElementById('searchInput').value = '';
    ajaxSearchMemos();
}
document.getElementById('searchInput').addEventListener('input', function() {
    ajaxSearchMemos();
});

// Polling new memos
function pollNewMemos() {
    setInterval(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'ajax_check_new_memos.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.new_memo_count > 0) {
                    document.getElementById('new-memo-text').textContent = 'You have ' + data.new_memo_count + ' new memo(s)!';
                    document.getElementById('new-memo-notification').style.display = 'block';
                    var upxhr = new XMLHttpRequest();
                    upxhr.open('GET', 'ajax_update_last_checked.php', true);
                    upxhr.send();
                }
            }
        };
        xhr.send();
    }, 30000); // Every 30 sec
}

// Preview memo
function showMemoPreview(id) {
    var content = document.getElementById('memo-content-' + id).innerHTML;
    var previewWindow = window.open('', 'Memo Preview', 'height=600,width=800');
    previewWindow.document.write('<html><head><title>Memo Preview</title>');
    previewWindow.document.write('<style>body{background:#fff;}.memo-main{width:760px;margin:40px auto;background:#fff;padding:40px;border:none;box-shadow:0 2px 8px rgba(0,0,0,0.07);}</style>');
    previewWindow.document.write('</head><body>');
    previewWindow.document.write(content);
    previewWindow.document.write('</body></html>');
    previewWindow.document.close();
    previewWindow.focus();
}

// Print memo
function printMemo(id) {
    var content = document.getElementById('memo-content-' + id).innerHTML;
    var printWindow = window.open('', 'Print Memo', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print Memo</title>');
    printWindow.document.write('<style>body{background:#fff;}.memo-main{width:760px;margin:40px auto;background:#fff;padding:40px;border:none;box-shadow:0 2px 8px rgba(0,0,0,0.07);}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.onload = function() { printWindow.print(); printWindow.close(); };
}

window.onload = function() {
    ajaxSearchMemos();
    pollNewMemos();
};
</script>
<?php include "../includes/user_footer.php"; ?>
</body>
</html>
