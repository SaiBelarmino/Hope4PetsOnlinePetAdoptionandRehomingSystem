<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class UploadPhotosController extends BaseController {
    public static function savePetPhotos(int $petId, int $shelterId, array $filesMeta): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO pet_photos (pet_id, shelter_id, file_name, file_path, created_at) VALUES (?,?,?,?,NOW())");
        if (!$stmt) return false;
        foreach ($filesMeta as $meta) {
            $stmt->bind_param('iiss', $petId, $shelterId, $meta['name'], $meta['path']);
            if (!$stmt->execute()) { $stmt->close(); return false; }
        }
        $stmt->close();
        return true;
    }
}
?>