<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class MessagesReplyController extends BaseController {
    public static function send(int $threadId, int $shelterId, string $message): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO messages (thread_id, sender_shelter_id, body, created_at) VALUES (?,?,?,NOW())");
        if (!$stmt) return false;
        $stmt->bind_param('iis', $threadId, $shelterId, $message);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>