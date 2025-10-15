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
                        u.full_name, u.profile_photo,
                        SUBSTRING_INDEX(m.body, '\n', 1) AS last_message,
                        MAX(m.created_at) AS last_time,
                        SUM(CASE WHEN m.recipient_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
                    FROM messages m
                    JOIN users u ON u.id = IF(m.sender_id = ?, m.recipient_id, m.sender_id)
                    WHERE m.sender_id = ? OR m.recipient_id = ?
                    GROUP BY other_user_id, u.full_name, u.profile_photo
                ) t
                ORDER BY last_time DESC
                LIMIT ?";
        return self::fetchAll($sql, 'iiiiii', [$userId, $userId, $userId, $userId, $userId, $limit]);
    }

    /**
     * Get recent contacts (users who have messaged with current user)
     */
    public static function getRecentContacts(int $userId, int $limit = 10): array {
        return self::fetchAll(
            "SELECT DISTINCT u.id, u.full_name, u.profile_photo
             FROM users u
             JOIN messages m ON (u.id = m.sender_id OR u.id = m.recipient_id)
             WHERE (m.sender_id = ? OR m.recipient_id = ?) AND u.id != ?
             ORDER BY m.created_at DESC
             LIMIT ?",
            'iiii',
            [$userId, $userId, $userId, $limit]
        );
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

    /**
     * Get all users except current (for contacts)
     */
    public static function getAllUsers(int $userId, int $limit = 20): array {
        return self::fetchAll(
            "SELECT id, full_name, profile_photo FROM users WHERE id != ? ORDER BY full_name LIMIT ?",
            'ii',
            [$userId, $limit]
        );
    }
}

// Ensure SessionManager is loaded if available and start session properly
if (file_exists(__DIR__ . '/../../config/SessionManager.php')) {
    require_once __DIR__ . '/../../config/SessionManager.php';
    if (class_exists('SessionManager') && method_exists('SessionManager', 'start')) {
        SessionManager::start();
    }
}

// AJAX endpoint: return conversation as JSON
if (php_sapi_name() !== 'cli' && isset($_GET['action']) && $_GET['action'] === 'conversation') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json; charset=utf-8');
    $currentUser = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $other = isset($_GET['other']) ? (int)$_GET['other'] : 0;
    if ($currentUser <= 0 || $other <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid users']);
        exit;
    }

    // Ensure current user is participant in conversation
    // Fetch messages only between current user and other
    $raw = PublicMessagesController::conversation($currentUser, $other, 1000);
    $out = [];
    if (!empty($raw) && is_array($raw)) {
        foreach ($raw as $r) {
            $senderId = (int)($r['sender_id'] ?? 0);
            $senderSummary = PublicMessagesController::userSummary($senderId);
            $senderName = $senderSummary['full_name'] ?? ($senderId === $currentUser ? 'You' : 'User');
            $out[] = [
                'id' => isset($r['id']) ? (int)$r['id'] : 0,
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'message' => $r['body'] ?? $r['message'] ?? '',
                'created_at' => $r['created_at'] ?? ''
            ];
        }
    }

    echo json_encode(['success' => true, 'messages' => $out]);
    exit;
}

// SSE endpoint: stream new messages for the current user with a specific other user
if (php_sapi_name() !== 'cli' && isset($_GET['action']) && $_GET['action'] === 'stream') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $currentUser = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $other = isset($_GET['other']) ? (int)$_GET['other'] : 0;
    $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

    if ($currentUser <= 0 || $other <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid users']);
        exit;
    }

    // Headers for SSE
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    header('Connection: keep-alive');
    @set_time_limit(0);

    $start = time();
    $timeout = 25; // seconds

    while ((time() - $start) < $timeout) {
        // Find messages with id > lastId between the two users
        $sql = "SELECT m.id, m.sender_id, m.recipient_id, m.body, m.created_at
                FROM messages m
                WHERE ((m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?))
                  AND (m.id > ? OR ? = 0)
                ORDER BY m.id ASC";
        $rows = self::fetchAll($sql, 'iiiiii', [$currentUser, $other, $other, $currentUser, $lastId, $lastId]);
        if (!empty($rows)) {
            $out = [];
            $maxId = $lastId;
            foreach ($rows as $r) {
                $senderId = (int)($r['sender_id'] ?? 0);
                $senderSummary = PublicMessagesController::userSummary($senderId);
                $senderName = $senderSummary['full_name'] ?? ($senderId === $currentUser ? 'You' : 'User');
                $mid = isset($r['id']) ? (int)$r['id'] : 0;
                if ($mid > $maxId) $maxId = $mid;
                $out[] = [
                    'id' => $mid,
                    'sender_id' => $senderId,
                    'sender_name' => $senderName,
                    'message' => $r['body'] ?? '',
                    'created_at' => $r['created_at'] ?? ''
                ];
            }
            // send new messages
            echo "data: " . json_encode(['success' => true, 'messages' => $out]) . "\n\n";
            @ob_flush(); @flush();
            // update lastId to newest sent
            $lastId = $maxId;
            // reset start so connection stays open longer while active
            $start = time();
            // continue loop to send further messages until timeout
        }
        // heartbeat to keep connection alive
        echo ": heartbeat\n\n";
        @ob_flush(); @flush();
        usleep(500000); // 0.5s
    }

    // no new messages within timeout (connection closing)
    echo "data: " . json_encode(['success' => true, 'messages' => []]) . "\n\n";
    @ob_flush(); @flush();
    exit;
}

?>
