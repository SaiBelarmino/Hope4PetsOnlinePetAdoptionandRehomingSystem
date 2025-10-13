<?php
// filepath: c:\xampp\htdocs\Hope4PetsOnlinePetAdoptionandRehomingSystem\public-users\controllers\EditPetManagementController.php

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/PetManagementController.php';

class EditPetManagementController extends BaseController {
    /**
     * Handle POST request from Edit Pet form
     */
    public static function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if (!$userId) {
            header('Location: ./login.php');
            exit;
        }

        $petId = (int)($_POST['pet_id'] ?? 0);
        if ($petId <= 0) {
            self::redirectWithError('Invalid pet ID');
        }

        // Verify ownership
        $pet = PetManagementController::getPetById($petId);
        if (!$pet || $pet['owner_id'] != $userId) {
            self::redirectWithError('You do not have permission to edit this pet.');
        }

        // basic validation & sanitize
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            self::redirectWithError('Name is required');
        }

        $data = [
            'shelter_id' => !empty($_POST['shelter_id']) ? (int)$_POST['shelter_id'] : null,
            'name' => $name,
            'species' => $_POST['species'] ?? 'other',
            'breed' => trim($_POST['breed'] ?? ''),
            'age' => trim($_POST['age'] ?? ''),
            'gender' => $_POST['gender'] ?? 'unknown',
            'size' => $_POST['size'] ?? 'medium',
            'vaccine_status' => trim($_POST['vaccine_status'] ?? ''),
            'health_status' => trim($_POST['health_status'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => $_POST['status'] ?? 'available',
        ];

        // update pet record
        $result = PetManagementController::updatePet($petId, $userId, $data);
        if (empty($result['success'])) {
            self::redirectWithError($result['message'] ?? 'Failed to update pet');
        }

        // handle photo uploads if any (optional for edit)
        if (!empty($_FILES['photos']['name'][0])) {
            $uploaded = self::processPhotosUpload($petId, $_FILES['photos'] ?? null, $userId);
            if ($uploaded === false) {
                // photo upload failed but pet updated; proceed with warning
                header('Location: ../views/PetManagement.php?success=1&warning=photo');
                exit;
            }
        }

        header('Location: ../views/PetManagement.php?success=1');
        exit;
    }

    /**
     * Process uploaded photos array and save to disk and DB (similar to add)
     */
    private static function processPhotosUpload(int $petId, $files, int $userId): bool {
        if (empty($files) || empty($files['name'])) return true; // no files is fine

        $baseDir = realpath(__DIR__ . '/../../storage') ?: __DIR__ . '/../../storage';
        $uploadDir = $baseDir . '/uploads/images/' . $userId;
        $publicPathPrefix = '/storage/uploads/images/' . $userId . '/';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) return false;
        }

        $allowedTypes = ['image/jpeg','image/png','image/webp','image/gif'];
        $primarySet = false;

        $names = $files['name'];
        if (!is_array($names)) $names = [$names];

        for ($i = 0; $i < count($names); $i++) {
            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            $type = is_array($files['type']) ? $files['type'][$i] : $files['type'];
            $origName = basename($names[$i]);

            if ($error !== UPLOAD_ERR_OK || !$tmpName) continue;
            if (!in_array($type, $allowedTypes)) continue;

            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            try {
                $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . ($ext ?: 'jpg');
            } catch (Exception $e) {
                $filename = time() . '_' . bin2hex(openssl_random_pseudo_bytes(6)) . '.' . ($ext ?: 'jpg');
            }

            $dest = $uploadDir . '/' . $filename;
            if (!move_uploaded_file($tmpName, $dest)) continue;

            $dbPath = $publicPathPrefix . $filename;
            $isPrimary = !$primarySet;
            if ($isPrimary) {
                $mysqli = PetManagementController::db();
                $stmt = $mysqli->prepare("UPDATE pets SET pet_photos = ? WHERE id = ? AND owner_id = ?");
                if ($stmt) {
                    $stmt->bind_param('sii', $dbPath, $petId, $userId);
                    $stmt->execute();
                    $stmt->close();
                    $primarySet = true;
                }
            }
        }

        return true;
    }

    private static function redirectWithError(string $msg): void {
        $qs = '?error=' . urlencode($msg);
        header('Location: ../views/PetManagement.php' . $qs);
        exit;
    }
}

// If this file is requested directly, handle the form submission
if (php_sapi_name() !== 'cli' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    EditPetManagementController::handleRequest();
}