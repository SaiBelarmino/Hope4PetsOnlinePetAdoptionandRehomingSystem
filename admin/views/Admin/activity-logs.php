<?php
// Protect this admin view from direct access: require admin session
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
require_once dirname(__DIR__, 2) . '/controllers/Admin/activity-logs-controller.php';
?>

<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
<?php include dirname(__DIR__, 2) . '/header.php'; ?>
<div class="container-fluid"><h3>Activity Logs</h3><p>Recent admin activities from admin_logs.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>