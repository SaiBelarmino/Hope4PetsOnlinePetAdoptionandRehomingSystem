<?php
// Protect this admin view from direct access: require admin session
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>

<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid"><h3>Activity Logs</h3><p>Recent admin activities from admin_logs.</p></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</div>
