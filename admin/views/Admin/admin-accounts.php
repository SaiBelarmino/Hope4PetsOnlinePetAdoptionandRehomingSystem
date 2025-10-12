<?php
// Protect this admin view from direct URL access
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
require_once dirname(__DIR__, 2) . '/controllers/Admin/admin-accounts-controller.php';
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
<div class="container-fluid"><h3>Manage Admin Accounts</h3><p>Create, edit, and deactivate admin accounts.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php include '../controllers/Admin/admin-accounts-controller.php'; ?>
