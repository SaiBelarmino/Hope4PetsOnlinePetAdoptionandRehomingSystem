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
<div class="container-fluid"><h3>Post Reactions</h3><p>Browse reactions and trends.</p></div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
<?php include '../controllers/Post/post-reactions-controller.php'; ?>
