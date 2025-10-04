<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class EditPetController extends BaseController {
    public static function update(int $petId, int $shelterId, array $data): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE pets SET name=?, species=?, breed=?, age=?, status=?, description=? WHERE id=? AND shelter_id=?");
        if (!$stmt) return false;
        $stmt->bind_param('sssissii', $data['name'], $data['species'], $data['breed'], $data['age'], $data['status'], $data['description'], $petId, $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows >= 0;
    }
}
?>