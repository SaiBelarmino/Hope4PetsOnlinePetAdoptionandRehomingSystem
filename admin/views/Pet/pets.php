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
<div class="container-fluid"><h3>All Pets</h3><p>Manage all pets.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php include '../controllers/Pet/pets-controller.php'; ?>
