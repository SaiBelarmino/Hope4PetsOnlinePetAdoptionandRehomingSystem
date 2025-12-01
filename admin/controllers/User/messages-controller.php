<?php

require_once __DIR__ . '/../../../controllers/BaseController.php';

class MessagesController extends BaseController {
    public static function latestThreads(int $limit = 50): array {
        $sql = "SELECT id, subject, created_at, updated_at FROM messages_threads ORDER BY updated_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }

    public static function getThreadParticipants(int $threadId): array {
        $sql = "SELECT u.full_name, u.profile_image 
                FROM messages_participants mp
                JOIN users u ON mp.user_id = u.id
                WHERE mp.thread_id = ?";
        return self::fetchAll($sql, 'i', [$threadId]);
    }
}
?>