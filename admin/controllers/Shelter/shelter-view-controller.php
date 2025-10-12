<?php


require_once __DIR__ . '/../../../config/SessionManager.php';
require_once __DIR__ . '/../../../controllers/BaseController.php';

SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

$shelterId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$shelter = null;
$pets = [];

if ($shelterId) {
    // fetch shelter with owner info
    $sql = "SELECT s.*, u.id AS owner_id, COALESCE(u.full_name, u.email, '') AS owner_name, u.email AS owner_email
            FROM shelters s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.id = ?";
    $shelter = BaseController::fetchOne($sql, 'i', [$shelterId]);

    // fetch pets for shelter
    $sqlPets = "SELECT id, name, species, breed, age, gender, status FROM pets WHERE shelter_id = ?";
    $pets = BaseController::fetchAll($sqlPets, 'i', [$shelterId]) ?: [];
}

// include the view that actually exists so variables are available in view scope
require_once __DIR__ . '/../views/shelters-view.php';
?>