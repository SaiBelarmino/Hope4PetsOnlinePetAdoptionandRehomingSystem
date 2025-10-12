<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
<?php include dirname(__DIR__, 2) . '/header.php'; ?>
<div class="container-fluid"><h3>Post Reports</h3><p>Flagged post reports.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php include '../controllers/Post/post-reports-controller.php'; ?>
