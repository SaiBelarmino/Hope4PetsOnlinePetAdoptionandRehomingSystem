<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class EditShelterController extends BaseController {
    public static function update(int $shelterId, array $data): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE shelters SET shelter_name = ?, address = ?, contact_number = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('sssi', $data['shelter_name'], $data['address'], $data['contact_number'], $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok !== false;
    }
}

// If called directly, handle JSON POST and return JSON response
try {
    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    // Read JSON body
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) $payload = $_POST;

    $shelterId = isset($payload['shelter_id']) ? (int)$payload['shelter_id'] : 0;
    $shelterName = isset($payload['shelter_name']) ? trim($payload['shelter_name']) : '';
    $address = isset($payload['address']) ? trim($payload['address']) : '';
    $contact = isset($payload['contact_number']) ? trim($payload['contact_number']) : '';

    if (!$shelterId || $shelterName === '') {
        echo json_encode(['success' => false, 'error' => 'Missing shelter id or name']);
        exit;
    }

    $data = [
        'shelter_name' => $shelterName,
        'address' => $address,
        'contact_number' => $contact
    ];

    $ok = EditShelterController::update($shelterId, $data);
    if ($ok) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>