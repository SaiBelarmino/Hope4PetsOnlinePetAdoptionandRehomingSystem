<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

/**
 * PublicMessagesController
 * Lightweight helper for direct user-to-user messaging using `messages` table.
 * Schema expected:
 *  messages(id, sender_id, recipient_id, body, is_read, created_at)
 */
class PublicMessagesController extends BaseController {

    /**
     * Send a message from one user to another.
     * Returns inserted message row or null on failure.
     */
    public static function send(int $senderId, int $recipientId, string $body): ?array {
        $body = trim($body);
        if ($senderId <= 0 || $recipientId <= 0 || $senderId === $recipientId || $body === '') {
            return null; // basic validation fail
        }

        $sql = 'INSERT INTO messages (sender_id, recipient_id, body) VALUES (?,?,?)';
        $mysqli = self::db();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('iis', $senderId, $recipientId, $body);
        if (!$stmt->execute()) { $stmt->close(); return null; }
        $insertId = (int)$stmt->insert_id;
        $stmt->close();
        return self::fetchOne('SELECT * FROM messages WHERE id=? LIMIT 1', 'i', [$insertId]);
    }

    /**
     * Fetch a conversation (all messages) between two users ordered oldest->newest.
     */
    public static function conversation(int $userId, int $otherUserId, int $limit = 200): array {
        if ($userId <=0 || $otherUserId <=0) return [];
        $sql = "SELECT m.*, 
                       CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END AS is_outgoing
                FROM messages m
                WHERE (m.sender_id=? AND m.recipient_id=?)
                   OR (m.sender_id=? AND m.recipient_id=?)
                ORDER BY m.id ASC
                LIMIT ?";
        return self::fetchAll($sql, 'iiiiii', [$userId, $userId, $otherUserId, $otherUserId, $userId, $limit]);
    }

    /**
     * List latest conversation partners with last message preview and unread count.
     */
    public static function inbox(int $userId, int $limit = 30): array {
        $sql = "SELECT * FROM (
                    SELECT 
                        IF(m.sender_id = ?, m.recipient_id, m.sender_id) AS other_user_id,
                        SUBSTRING_INDEX(m.body, '\n', 1) AS last_message,
                        MAX(m.created_at) AS last_time,
                        SUM(CASE WHEN m.recipient_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
                    FROM messages m
                    WHERE m.sender_id = ? OR m.recipient_id = ?
                    GROUP BY other_user_id
                ) t
                ORDER BY last_time DESC
                LIMIT ?";
        return self::fetchAll($sql, 'iiiii', [$userId, $userId, $userId, $userId, $limit]);
    }

    /**
     * Mark all messages from $otherUserId to $userId as read. Returns number updated.
     */
    public static function markAsRead(int $userId, int $otherUserId): int {
        $mysqli = self::db();
        $stmt = $mysqli->prepare('UPDATE messages SET is_read=1 WHERE recipient_id=? AND sender_id=? AND is_read=0');
        if (!$stmt) return 0;
        $stmt->bind_param('ii', $userId, $otherUserId);
        if (!$stmt->execute()) { $stmt->close(); return 0; }
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    /**
     * Helper: Basic user fetch (id + display fields) to show in UI.
     */
    public static function userSummary(int $userId): ?array {
        return self::fetchOne('SELECT id, full_name, profile_photo FROM users WHERE id=? LIMIT 1', 'i', [$userId]);
    }
}
?>
