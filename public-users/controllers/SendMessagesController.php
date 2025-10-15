<?php
/**
 * Messages Controller
 * 
 * Handles messaging between users.
 * Each user can only see their own sent/received messages.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';

class MessagesController extends BaseController {
    /**
     * Send a message
     */
    public static function sendMessage(int $senderId, int $recipientId, string $body): array {
        $mysqli = self::db();
        
        if (empty(trim($body))) {
            return ['success' => false, 'message' => 'Message cannot be empty.'];
        }
        
        // Try normal insert first
        $stmt = $mysqli->prepare(
            "INSERT INTO messages (sender_id, recipient_id, body, is_read, created_at) 
             VALUES (?, ?, ?, 0, NOW())"
        );
        
        if ($stmt) {
            $stmt->bind_param('iis', $senderId, $recipientId, $body);
            $success = $stmt->execute();
            if ($success) {
                $messageId = $mysqli->insert_id;
                $stmt->close();
                return [
                    'success' => true,
                    'message' => 'Message sent!',
                    'message_id' => $messageId
                ];
            }
            // capture error and fall through to fallback logic
            $dberr = $mysqli->error;
            $stmt->close();
        } else {
            $dberr = $mysqli->error;
        }

        // Log DB error for debugging
        @file_put_contents(__DIR__ . '/send_msg_db_error.log', date('c') . " ERROR: " . ($dberr ?? 'prepare failed') . PHP_EOL, FILE_APPEND);

        // Fallback: if error indicates missing default for id (no AUTO_INCREMENT), try to compute next id and insert explicitly
        $errText = strtolower((string)($dberr ?? ''));
        if (strpos($errText, "doesn't have a default value") !== false || strpos($errText, 'cannot be null') !== false || strpos($errText, 'field') !== false) {
            try {
                $res = $mysqli->query('SELECT COALESCE(MAX(id),0) + 1 AS next_id FROM messages');
                $row = $res ? $res->fetch_assoc() : null;
                $nextId = (int)($row['next_id'] ?? 0);
                if ($nextId <= 0) $nextId = 1;

                $stmt2 = $mysqli->prepare(
                    "INSERT INTO messages (id, sender_id, recipient_id, body, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())"
                );
                if ($stmt2) {
                    // use string for body
                    $stmt2->bind_param('iiis', $nextId, $senderId, $recipientId, $body);
                    $ok = $stmt2->execute();
                    $stmt2->close();
                    if ($ok) {
                        return [
                            'success' => true,
                            'message' => 'Message sent!',
                            'message_id' => $nextId
                        ];
                    }
                    @file_put_contents(__DIR__ . '/send_msg_db_error.log', date('c') . " RETRY-ERROR: " . $mysqli->error . PHP_EOL, FILE_APPEND);
                }
            } catch (Throwable $e) {
                @file_put_contents(__DIR__ . '/send_msg_db_error.log', date('c') . " EXCEPTION: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
        }

        return ['success' => false, 'message' => 'Failed to send message. Server error logged.'];
    }
    
    /**
     * Get conversations for a user (grouped by other participant)
     */
    public static function getConversations(int $userId): array {
        return self::fetchAll(
            "SELECT 
                CASE 
                    WHEN m.sender_id = ? THEN m.recipient_id
                    ELSE m.sender_id
                END as other_user_id,
                u.full_name as other_user_name,
                u.profile_photo as other_user_photo,
                MAX(m.created_at) as last_message_time,
                (SELECT body FROM messages WHERE 
                    (sender_id = ? AND recipient_id = other_user_id) OR 
                    (sender_id = other_user_id AND recipient_id = ?)
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                SUM(CASE WHEN m.recipient_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
             FROM messages m
             JOIN users u ON u.id = CASE 
                WHEN m.sender_id = ? THEN m.recipient_id
                ELSE m.sender_id
             END
             WHERE m.sender_id = ? OR m.recipient_id = ?
             GROUP BY other_user_id, u.full_name, u.profile_photo
             ORDER BY last_message_time DESC",
            'iiiiiii',
            [$userId, $userId, $userId, $userId, $userId, $userId, $userId]
        );
    }
    
    /**
     * Get messages between two users
     */
    public static function getMessagesBetweenUsers(int $userId1, int $userId2, int $limit = 50): array {
        return self::fetchAll(
            "SELECT m.*, 
                    s.full_name as sender_name, s.profile_photo as sender_photo,
                    r.full_name as recipient_name, r.profile_photo as recipient_photo
             FROM messages m
             LEFT JOIN users s ON m.sender_id = s.id
             JOIN users r ON m.recipient_id = r.id
             WHERE (m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?)
             ORDER BY m.created_at ASC
             LIMIT ?",
            'iiiii',
            [$userId1, $userId2, $userId2, $userId1, $limit]
        );
    }
    
    /**
     * Mark messages as read
     */
    public static function markAsRead(int $recipientId, int $senderId): bool {
        $mysqli = self::db();
        
        $stmt = $mysqli->prepare(
            "UPDATE messages SET is_read = 1 
             WHERE recipient_id = ? AND sender_id = ? AND is_read = 0"
        );
        
        if (!$stmt) return false;
        
        $stmt->bind_param('ii', $recipientId, $senderId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Get unread message count
     */
    public static function getUnreadCount(int $userId): int {
        return (int)self::fetchValue(
            "SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0",
            'i',
            [$userId],
            0
        );
    }
    
    /**
     * Delete message (soft delete - only hides from sender's view)
     */
    public static function deleteMessage(int $messageId, int $userId): array {
        $mysqli = self::db();
        
        // Verify user owns the message
        $message = self::fetchOne(
            "SELECT * FROM messages WHERE id = ? AND sender_id = ?",
            'ii',
            [$messageId, $userId]
        );
        
        if (!$message) {
            return ['success' => false, 'message' => 'Message not found or you do not have permission.'];
        }
        
        // For now, actually delete. In production, you might want soft delete
        $stmt = $mysqli->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error.'];
        }
        
        $stmt->bind_param('ii', $messageId, $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return ['success' => $success, 'message' => $success ? 'Message deleted!' : 'Failed to delete message.'];
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
}

// Ensure SessionManager if available
if (file_exists(__DIR__ . '/../../config/SessionManager.php')) {
    require_once __DIR__ . '/../../config/SessionManager.php';
    if (class_exists('SessionManager') && method_exists('SessionManager', 'start')) {
        SessionManager::start();
    }
}

// Handle direct POST when this file is the form action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic session start/read. Adjust if your app uses a SessionManager.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
    $petId = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : 0;
    $messageBody = isset($_POST['message']) ? trim($_POST['message']) : '';

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($currentUserId <= 0) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You must be logged in to send messages.']);
            exit;
        }
        // redirect back to login or referer
        $redirect = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $redirect);
        exit;
    }

    if ($recipientId <= 0 || $messageBody === '') {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid recipient or empty message.']);
            exit;
        }
        $redirect = $_SERVER['HTTP_REFERER'] ?? './';
        header('Location: ' . $redirect);
        exit;
    }

    // Rate limiting: prevent rapid duplicate sends
    try {
        $mysqli = MessagesController::db();
        // last message to same recipient
        $stmt = $mysqli->prepare('SELECT id, body, UNIX_TIMESTAMP(created_at) AS ts FROM messages WHERE sender_id=? AND recipient_id=? ORDER BY id DESC LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('ii', $currentUserId, $recipientId);
            $stmt->execute();
            $res = $stmt->get_result();
            $last = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            $now = time();
            if ($last) {
                $lastTs = (int)($last['ts'] ?? 0);
                $lastBody = trim((string)($last['body'] ?? ''));
                // block if exactly the same body sent within 10 seconds
                if ($lastBody !== '' && $lastBody === $messageBody && ($now - $lastTs) < 10) {
                    $result = ['success' => false, 'message' => 'Please do not send duplicate messages.'];
                    if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                    $redirect = $_SERVER['HTTP_REFERER'] ?? './'; header('Location: ' . $redirect . (strpos($redirect,'?')===false?'?':'&').'error=duplicate'); exit;
                }
                // block very rapid sends (same recipient) within 2 seconds
                if (($now - $lastTs) < 2) {
                    $result = ['success' => false, 'message' => 'You are sending messages too quickly. Please wait a moment.'];
                    if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                    $redirect = $_SERVER['HTTP_REFERER'] ?? './'; header('Location: ' . $redirect . (strpos($redirect,'?')===false?'?':'&').'error=rate'); exit;
                }
            }
        }

        // global rate: count messages by sender in last 10 seconds
        $stmt2 = $mysqli->prepare('SELECT COUNT(*) AS cnt FROM messages WHERE sender_id=? AND created_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)');
        if ($stmt2) {
            $stmt2->bind_param('i', $currentUserId);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $row2 = $res2 ? $res2->fetch_assoc() : null;
            $stmt2->close();
            $cnt = (int)($row2['cnt'] ?? 0);
            if ($cnt >= 6) { // more than 5 messages in 10s
                $result = ['success' => false, 'message' => 'You are sending messages too quickly. Slow down.'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                $redirect = $_SERVER['HTTP_REFERER'] ?? './'; header('Location: ' . $redirect . (strpos($redirect,'?')===false?'?':'&').'error=rate2'); exit;
            }
        }
    } catch (Throwable $e) {
        // ignore rate-check errors and proceed (fail-open), but log in production
    }

    // Call the controller's sendMessage method
    try {
        $result = MessagesController::sendMessage($currentUserId, $recipientId, $messageBody);
    } catch (Throwable $e) {
        $result = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }

    // Ensure $result includes id and created_at
    if ($result && !empty($result['id'])) {
        $out = ['success' => true, 'message' => 'Message sent', 'message_id' => (int)$result['id'], 'created_at' => $result['created_at'] ?? null];
    } else {
        // try to get last inserted id by matching recent row
        try {
            $mysqli = MessagesController::db();
            $stmt = $mysqli->prepare('SELECT id, created_at FROM messages WHERE sender_id=? AND recipient_id=? AND body=? ORDER BY id DESC LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('iis', $currentUserId, $recipientId, $messageBody);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();
                if ($row) {
                    $out = ['success' => true, 'message' => 'Message sent', 'message_id' => (int)$row['id'], 'created_at' => $row['created_at'] ?? null];
                }
            }
        } catch (Throwable $e) {
            // ignore
        }
    }

    if (empty($out)) {
        // Attempt to capture DB error information for debugging
        try {
            $mysqli = MessagesController::db();
            $dberr = '';
            if ($mysqli && is_object($mysqli) && property_exists($mysqli, 'error')) {
                $dberr = $mysqli->error;
            }
            if ($dberr) {
                @file_put_contents(__DIR__ . '/send_msg_db_error.log', date('c') . " ERROR: " . $dberr . PHP_EOL, FILE_APPEND);
                $out = ['success' => false, 'message' => 'Database error: ' . $dberr];
            } else {
                $out = ['success' => false, 'message' => 'Failed to send message'];
            }
        } catch (Throwable $e) {
            $out = ['success' => false, 'message' => 'Failed to send message'];
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($out);
        exit;
    }

    // On regular form submit, redirect back to referer (chat view)
    $redirect = $_SERVER['HTTP_REFERER'] ?? './ChatMessages.php';
    if (!empty($out['success'])) {
        $glue = (strpos($redirect, '?') === false) ? '?' : '&';
        $redirect .= $glue . 'sent=1';
    } else {
        $glue = (strpos($redirect, '?') === false) ? '?' : '&';
        $redirect .= $glue . 'error=' . urlencode($out['message'] ?? 'Unable to send');
    }

    header('Location: ' . $redirect);
    exit;
}
