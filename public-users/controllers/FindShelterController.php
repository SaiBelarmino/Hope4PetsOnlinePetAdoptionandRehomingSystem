<?php
// Refactored to use SessionManager and BaseController pattern
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/BaseController.php';

SessionManager::init();

class FindShelterController extends BaseController {
    // Build and return data used by the view or API consumers
    public static function fetchData(): array {
        $userId = 0;
        try { $userId = (int)SessionManager::getUserId(); } catch (Throwable $e) { $userId = 0; }
        $conn = self::db();

        // If requesting pets for a specific shelter
        if (isset($_GET['pets']) && isset($_GET['shelter_id'])) {
            $shelterId = intval($_GET['shelter_id']);
            $pets = [];

            $check = $conn->prepare("SELECT is_verified, user_id FROM shelters WHERE id = ? LIMIT 1");
            if ($check) {
                $check->bind_param('i', $shelterId);
                $check->execute();
                $res = $check->get_result();
                $row = $res->fetch_assoc();
                $check->close();

                $isVerified = (int)($row['is_verified'] ?? 0);
                $ownerId = isset($row['user_id']) ? intval($row['user_id']) : 0;

                // Allow pets to be returned if shelter is verified, OR the requester is logged-in
                if ($isVerified === 1 || ($userId > 0)) {
                    $sql = "SELECT id, name, type, breed, age, gender, description, photo FROM pets WHERE shelter_id = ? AND status = 'available' ORDER BY name ASC";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param('i', $shelterId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($r = $result->fetch_assoc()) {
                            $pets[] = $r;
                        }
                        $stmt->close();
                    }
                }
            }

            return $pets;
        }

        // Otherwise return list of shelters
        $shelters = [];
        $baseSql = "SELECT s.id, s.shelter_name, s.address, s.contact_number, s.is_verified, s.user_id AS owner_id, u.full_name AS owner_name, 
            (SELECT COUNT(*) FROM pets WHERE shelter_id = s.id) AS pet_count
            FROM shelters s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE (s.is_verified = 1 AND s.verified_at IS NOT NULL)";

        if ($userId > 0) {
            // Logged-in users can also see unverified shelters
            $baseSql = "SELECT s.id, s.shelter_name, s.address, s.contact_number, s.is_verified, s.user_id AS owner_id, u.full_name AS owner_name, 
            (SELECT COUNT(*) FROM pets WHERE shelter_id = s.id) AS pet_count
            FROM shelters s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE 1"; // no verification filter for logged-in users
        }

        $baseSql .= " ORDER BY s.shelter_name ASC";

        if ($userId > 0) {
            $stmt = $conn->prepare($baseSql);
            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $row['is_owner'] = (isset($row['owner_id']) && intval($row['owner_id']) === $userId) ? 1 : 0;
                    $shelters[] = $row;
                }
                $stmt->close();
            }
        } else {
            $result = $conn->query($baseSql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row['is_owner'] = 0;
                    $shelters[] = $row;
                }
            }
        }

        return $shelters;
    }

    public static function handle(): void {
        // Public view - do not require login
        $data = self::fetchData();
        // expose guard to view if needed
        if (!defined('ALLOW_RENDER_FIND_SHELTER')) define('ALLOW_RENDER_FIND_SHELTER', true);
        include __DIR__ . '/../views/FindShelters.php';
    }
}

// If this controller is called directly serve JSON (backend API)
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    // Session already initialized above
    $data = FindShelterController::fetchData();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
