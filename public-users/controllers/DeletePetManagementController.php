<?php
// filepath: c:\xampp\htdocs\Hope4PetsOnlinePetAdoptionandRehomingSystem\public-users\controllers\DeletePetManagementController.php

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/PetManagementController.php';

class DeletePetManagementController extends BaseController {
    /**
     * Handle POST request to delete a pet
     */
    public static function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if (!$userId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }

        $petId = (int)($_POST['pet_id'] ?? 0);
        if ($petId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid pet ID']);
            exit;
        }

        // Delete the pet
        $result = PetManagementController::deletePet($petId, $userId);
        header('Content-Type: application/json');
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['message']]);
        }
        exit;
    }
}

// If this file is requested directly, handle the form submission
if (php_sapi_name() !== 'cli' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    DeletePetManagementController::handleRequest();
}