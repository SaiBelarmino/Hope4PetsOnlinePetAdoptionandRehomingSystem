<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class MessagesInboxController extends BaseController {
    public static function listThreads(int $shelterId, int $limit = 50): array {
        $sql = "SELECT id, subject, updated_at FROM messages_threads WHERE shelter_id=? ORDER BY updated_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$shelterId, $limit]);
    }
}
?>