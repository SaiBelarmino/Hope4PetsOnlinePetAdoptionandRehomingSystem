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
<div class="container-fluid"><h3>Adoption Requests</h3><p>Manage adoption requests.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php
include '../controllers/Adoption/adoption-requests-controller.php';
?>
