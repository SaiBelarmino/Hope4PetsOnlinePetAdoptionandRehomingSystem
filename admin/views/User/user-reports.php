<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php
include dirname(__DIR__, 2) . '/header.php';
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
<div class="container-fluid"><h3>User Reports</h3><p>Flagged user content and abuse reports.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php include '../controllers/User/user-reports-controller.php'; ?>
