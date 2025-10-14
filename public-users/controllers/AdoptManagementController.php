<?php
require_once __DIR__ . '/../../controllers/BaseController.php';


class AdoptController extends BaseController {
    public static function details(int $petId): ?array {
        return self::fetchOne("SELECT id, name, species, breed, age, status, description FROM pets WHERE id=?", 'i', [$petId]);
    }

    public static function request(int $userId, int $petId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO adoption_requests (user_id, pet_id, status, created_at) VALUES (?,?, 'pending', NOW())");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $userId, $petId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>