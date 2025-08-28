<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
include "../includes/user_sidebar.php";
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>MemoGen User Panel</title>
    <link rel="stylesheet" href="includes/user_style.css">
    <style>
        .dashboard-metrics { display: flex; gap: 28px; margin-bottom: 36px; flex-wrap: wrap; }
        .metric-card { background: #f7f7fb; border-radius: 8px; padding: 32px 24px; min-width: 180px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); text-align: center; }
        .metric-card h2 { font-size: 2.8rem; color: #1976d2; margin: 0 0 8px 0; }
        .metric-card span { font-size: 1.15rem; color: #444; }
    </style>
</head>
<body>
<div class="container">
    <h2>User Dashboard</h2>
    <div class="dashboard-metrics" id="dashboard-metrics">
        <div class="metric-card">
            <h2 id="memo-count">...</h2>
            <span>My Memorandums</span>
        </div>
        <div class="metric-card">
            <h2 id="notif-count">...</h2>
            <span>Unread Notifications</span>
        </div>
    </div>
</div>
<?php include "../includes/user_footer.php"; ?>

<script>
function loadMetrics() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'ajax_dashboard_metrics.php', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                document.getElementById('memo-count').textContent = data.total_memos;
                document.getElementById('notif-count').textContent = data.total_notifications;
            } catch(e) {
                document.getElementById('memo-count').textContent = "Error";
                document.getElementById('notif-count').textContent = "Error";
            }
        }
    };
    xhr.send();
}
// Initial load
loadMetrics();
// Refresh every 30 seconds
setInterval(loadMetrics, 30000);
</script>
</body>
</html>