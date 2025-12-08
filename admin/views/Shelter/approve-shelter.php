<?php

require_once __DIR__ . '/../../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/Shelter/shelters-controller.php';
require_once __DIR__ . '/../../../controllers/BaseController.php';

SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    // Approve shelter
    $sql1 = "UPDATE shelters SET is_verified = 1, verified_at = NOW() WHERE id = ?";
    BaseController::execute($sql1, 'i', [$id]);

    // Approve all shelter documents
    $sql2 = "UPDATE shelter_documents SET status = 'approved' WHERE shelter_id = ?";
    BaseController::execute($sql2, 'i', [$id]);

    header("Location: shelters.php?msg=approved");
    exit;
}
header("Location: shelters.php?msg=error");
exit;