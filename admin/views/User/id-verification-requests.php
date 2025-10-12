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
<div class="container-fluid"><h3>ID Verification Requests</h3><p>Queue of user ID verifications.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php
include dirname(__DIR__, 2) . '/controllers/User/id-verification-requests-controller.php';
?>
