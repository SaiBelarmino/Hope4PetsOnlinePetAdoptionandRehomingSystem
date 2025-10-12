<?php

require_once __DIR__ . '/../../../controllers/BaseController.php';

class MessagesController extends BaseController {
    public static function latestThreads(int $limit = 50): array {
        $sql = "SELECT id, subject, created_at, updated_at FROM messages_threads ORDER BY updated_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>