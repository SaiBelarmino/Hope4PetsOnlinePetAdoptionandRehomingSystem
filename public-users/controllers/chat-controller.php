<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ChatController extends BaseController {
    public static function recentThreads(int $userId, int $limit = 50): array {
        $sql = "SELECT id, subject, updated_at FROM messages_threads WHERE user_id=? ORDER BY updated_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$userId, $limit]);
    }
}
?>