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
<div class="container-fluid"><h3>Donations by Shelter</h3><p>Breakdown of donations per shelter.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php
include '../controllers/Adoption/donations-by-shelter-controller.php';
?>
