<?php
require_once __DIR__ . '/../../controllers/BaseController.php';


class AdoptController extends BaseController {
    public static function details(int $petId): ?array {
        return self::fetchOne("SELECT id, name, species, breed, age, status, description FROM pets WHERE id=?", 'i', [$petId]);
    }

    /**
     * Create an adoption request.
     * Returns the inserted adoption_request id on success, or false on failure.
     */
    public static function request(int $userId, int $petId, array $opts = []) {
        $mysqli = self::db();

        // Try to determine shelter_id from pet if available
        $shelterId = null;
        $pet = self::fetchOne("SELECT shelter_id, status FROM pets WHERE id = ?", 'i', [$petId]);
        if ($pet) {
            $shelterId = $pet['shelter_id'] ?? null;
            // prevent requests on non-available pets
            if (($pet['status'] ?? '') !== 'available') {
                return false;
            }
        }

        // Ensure adoption_requests table exists, attempt to create a minimal compatible schema if missing
        $createSql = "CREATE TABLE IF NOT EXISTS adoption_requests (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            pet_id BIGINT(20) UNSIGNED NOT NULL,
            shelter_id BIGINT(20) UNSIGNED DEFAULT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $mysqli->query($createSql);

        if ($shelterId === null) {
            $stmt = $mysqli->prepare("INSERT INTO adoption_requests (user_id, pet_id, status, created_at) VALUES (?,?, 'pending', NOW())");
            if (!$stmt) return false;
            $stmt->bind_param('ii', $userId, $petId);
            $ok = $stmt->execute();
        } else {
            $stmt = $mysqli->prepare("INSERT INTO adoption_requests (user_id, pet_id, shelter_id, status, created_at) VALUES (?,?,?, 'pending', NOW())");
            if (!$stmt) return false;
            $stmt->bind_param('iii', $userId, $petId, $shelterId);
            $ok = $stmt->execute();
        }
        if (!$ok) { $stmt->close(); return false; }
        $insertId = $mysqli->insert_id;
        $stmt->close();

        return $insertId;
    }
}
?>

<?php
// Simple HTTP handler for form posts. This allows posting directly to this file from views.
if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    // Basic flash helper (very small)
    if (!function_exists('set_flash')) {
        function set_flash($type, $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
    }

    $action = $_POST['action'] ?? null;
    if ($action === 'request') {
        $petId = (int)($_POST['pet_id'] ?? 0);
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            set_flash('error', 'You must be logged in to request adoption.');
            header('Location: ../views/pets.php');
            exit;
        }
        // Ensure the user has a verified account before accepting adoption requests
        $isVerifiedInSession = !empty($_SESSION['user']['is_verified']) && $_SESSION['user']['is_verified'];
        if (!$isVerifiedInSession) {
            // Re-check the database in case an admin just approved the user
            require_once __DIR__ . '/../../config/db-connection/db_connection.php';
            $stmt = $conn->prepare("SELECT is_verified FROM users WHERE id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();
                if ($row && !empty($row['is_verified'])) {
                    // update session so UI/other checks reflect the change immediately
                    $_SESSION['user']['is_verified'] = (int)$row['is_verified'];
                    $isVerifiedInSession = true;
                }
            }
        }

        if (!$isVerifiedInSession) {
            set_flash('error', 'Your account must be verified before submitting an adoption request. Please upload your ID on your profile.');
            header('Location: ../views/MyProfile.php');
            exit;
        }
        // Collect applicant form fields (reasonable defaults)
        $applicant_name = trim($_POST['applicant_name'] ?? '');
        $applicant_phone = trim($_POST['applicant_phone'] ?? '');
        $applicant_address = trim($_POST['applicant_address'] ?? '');
        $applicant_message = trim($_POST['applicant_message'] ?? '');

        $insertId = AdoptController::request($userId, $petId, [
            'applicant_name' => $applicant_name,
            'applicant_phone' => $applicant_phone,
            'applicant_address' => $applicant_address,
            'applicant_message' => $applicant_message
        ]);
        if ($insertId === false) {
            set_flash('error', 'Failed to submit adoption request. Please try again.');
            header('Location: ../views/pets.php');
            exit;
        }
    // Ensure meta table exists (simple and safe create-if-not-exists)
    // use project's DB connection in global scope
    require_once __DIR__ . '/../../config/db-connection/db_connection.php';
    $mysqli = $conn;
        $createSql = "CREATE TABLE IF NOT EXISTS adoption_request_meta (
            id INT PRIMARY KEY AUTO_INCREMENT,
            adoption_request_id INT NOT NULL,
            meta_key VARCHAR(191) NOT NULL,
            meta_value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (adoption_request_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $mysqli->query($createSql);

        // Insert meta rows
        $metaStmt = $mysqli->prepare("INSERT INTO adoption_request_meta (adoption_request_id, meta_key, meta_value) VALUES (?,?,?)");
        if ($metaStmt) {
            $metaStmt->bind_param('iss', $insertId, $k, $v);

            $k = 'applicant_name'; $v = $applicant_name; $metaStmt->execute();
            $k = 'applicant_phone'; $v = $applicant_phone; $metaStmt->execute();
            $k = 'applicant_address'; $v = $applicant_address; $metaStmt->execute();
            $k = 'applicant_message'; $v = $applicant_message; $metaStmt->execute();

            $metaStmt->close();
        }

        set_flash('success', 'Adoption request submitted. The shelter/owner will be notified.');
        header('Location: ../views/pets.php');
        exit;
    }
}