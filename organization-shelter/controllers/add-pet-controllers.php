<?php
// AddPetController: handles adding a new pet listing for the shelter.
require_once __DIR__ . '/../../controllers/BaseController.php';

class AddPetController extends BaseController {
    public static function create(array $data): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO pets (shelter_id, name, species, breed, age, status, description, created_at) VALUES (?,?,?,?,?,?,?,NOW())");
        if (!$stmt) return false;
        $status = $data['status'] ?? 'available';
        $stmt->bind_param(
            'isssiss',
            $data['shelter_id'],
            $data['name'],
            $data['species'],
            $data['breed'],
            $data['age'],
            $status,
            $data['description']
        );
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>