<?php
// Public serve endpoint for shelter documents (still requires admin session)
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

// Reuse the logic from admin/controllers/serve-shelter-document.php but with adjusted paths
require_once __DIR__ . '/../admin/controllers/serve-shelter-document.php';

?>