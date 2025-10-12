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
<div class="container-fluid"><h3>Profile Settings</h3><p>Update admin profile.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php
include '../controllers/Admin/profile-settings-controller.php';
?>
