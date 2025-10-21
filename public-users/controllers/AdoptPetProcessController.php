<?php
// AdoptPetProcessController.php - handles adoption requests from public users

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/PetManagementController.php';

class AdoptPetProcessController extends BaseController {
    /**
     * Handle POST request from adoption form
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
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'You must be logged in to submit an adoption request.'];
            header('Location: ./login.php');
            exit;
        }

        $action = $_POST['action'] ?? '';
        if ($action !== 'request') {
            self::redirectWithError('Invalid action.');
        }

        $petId = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : 0;
        $applicantName = trim($_POST['applicant_name'] ?? '');
        $applicantPhone = trim($_POST['applicant_phone'] ?? '');
        $applicantAddress = trim($_POST['applicant_address'] ?? '');
        $applicantMessage = trim($_POST['applicant_message'] ?? '');

        if ($petId <= 0 || $applicantName === '' || $applicantPhone === '' || $applicantAddress === '') {
            self::redirectWithError('Missing required fields.');
        }

        // get mysqli from existing controller
        $mysqli = PetManagementController::db();
        if (!$mysqli) {
            self::redirectWithError('Database connection not available.');
        }

        // check pet existence and availability
        $stmt = $mysqli->prepare('SELECT id, status, shelter_id FROM pets WHERE id = ? LIMIT 1');
        if (!$stmt) {
            self::redirectWithError('Failed to prepare statement (pet lookup).');
        }
        $stmt->bind_param('i', $petId);
        $stmt->execute();
        $res = $stmt->get_result();
        $pet = $res->fetch_assoc();
        $stmt->close();

        if (!$pet) {
            self::redirectWithError('Pet not found.');
        }

        if (($pet['status'] ?? '') !== 'available') {
            self::redirectWithError('This pet is not currently available for adoption.');
        }

        // prevent duplicate active applications
        $chk = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM adoptions WHERE pet_id = ? AND applicant_id = ? AND status IN ('applied','approved')");
        if (!$chk) {
            self::redirectWithError('Failed to prepare statement (duplicate check).');
        }
        $chk->bind_param('ii', $petId, $userId);
        $chk->execute();
        $r = $chk->get_result()->fetch_assoc();
        $chk->close();
        $exists = (int)($r['cnt'] ?? 0);
        if ($exists > 0) {
            self::redirectWithError('You have already submitted an adoption request for this pet.');
        }

        // insert adoption record
        $ins = $mysqli->prepare('INSERT INTO adoptions (pet_id, applicant_id, shelter_id, status, created_at) VALUES (?, ?, ?, ?, NOW())');
        if (!$ins) {
            self::redirectWithError('Failed to prepare statement (insert adoption).');
        }
        $status = 'applied';
        $shelterId = isset($pet['shelter_id']) ? ($pet['shelter_id'] !== '' ? (int)$pet['shelter_id'] : null) : null;
        // bind_param does not accept null types easily; use integer or null via s and convert
        if ($shelterId === null) {
            // bind as null string
            $shelterParam = null;
            $ins->bind_param('iiss', $petId, $userId, $shelterParam, $status);
        } else {
            $ins->bind_param('iiis', $petId, $userId, $shelterId, $status);
        }

        if (!$ins->execute()) {
            $ins->close();
            self::redirectWithError('Failed to create adoption request.');
        }

        $adoptionId = $ins->insert_id;
        $ins->close();

        // store applicant details if table exists (best-effort)
        try {
            // create table if not exists (best-effort)
            $mysqli->query("CREATE TABLE IF NOT EXISTS adoption_applicants (
                id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                adoption_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(255),
                phone VARCHAR(100),
                address TEXT,
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $s = $mysqli->prepare('INSERT INTO adoption_applicants (adoption_id, name, phone, address, message) VALUES (?, ?, ?, ?, ?)');
            if ($s) {
                $s->bind_param('issss', $adoptionId, $applicantName, $applicantPhone, $applicantAddress, $applicantMessage);
                $s->execute();
                $s->close();
            }
        } catch (Throwable $e) {
            // ignore errors here
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Adoption request submitted successfully.'];
        $referer = $_SERVER['HTTP_REFERER'] ?? '../views/BrowsePet.php';
        header('Location: ' . $referer);
        exit;
    }

    private static function redirectWithError(string $msg): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = ['type' => 'error', 'message' => $msg];
        $referer = $_SERVER['HTTP_REFERER'] ?? '../views/BrowsePet.php';
        header('Location: ' . $referer);
        exit;
    }
}

// If requested directly, handle the form submission
if (php_sapi_name() !== 'cli' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    AdoptPetProcessController::handleRequest();
}
