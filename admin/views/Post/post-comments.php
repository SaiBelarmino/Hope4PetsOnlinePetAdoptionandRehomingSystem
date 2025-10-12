<?php
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid"><h3>Post Comments</h3><p>View and moderate comments.</p></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</div>
<?php
include '../controllers/Post/post-comments-controller.php';
?>
