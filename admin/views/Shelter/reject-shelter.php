<?php

require_once __DIR__ . '/../../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/Shelter/shelters-controller.php';
require_once __DIR__ . '/../../../controllers/BaseController.php';

SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    // Delete shelter
    $sql1 = "DELETE FROM shelters WHERE id = ?";
    BaseController::execute($sql1, 'i', [$id]);

    // Delete shelter documents
    $sql2 = "DELETE FROM shelter_documents WHERE shelter_id = ?";
    BaseController::execute($sql2, 'i', [$id]);

    header("Location: shelters.php?msg=rejected");
    exit;
}
header("Location: shelters.php?msg=error");
exit;