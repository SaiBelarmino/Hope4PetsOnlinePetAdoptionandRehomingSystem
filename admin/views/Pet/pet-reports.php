<?php
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid"><h3>Pet Reports</h3><p>Flagged pet-related reports.</p></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</div>
<?php include '../controllers/Pet/pet-reports-controller.php'; ?>
