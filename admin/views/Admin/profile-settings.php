<?php
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid"><h3>Profile Settings</h3><p>Update admin profile.</p></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</div>
<?php
include '../controllers/Admin/profile-settings-controller.php';
?>
