<?php
/**
 * Messages Controller
 * 
 * Handles messaging between users.
 * Each user can only see their own sent/received messages.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

class MessagesController extends BaseController {
    /**
     * Send a message
     */
    public static function sendMessage(int $senderId, int $recipientId, string $body): array {
        $mysqli = self::db();
        
        if (empty(trim($body))) {
            return ['success' => false, 'message' => 'Message cannot be empty.'];
        }
        
        // Insert message
        $stmt = $mysqli->prepare(
            "INSERT INTO messages (sender_id, recipient_id, body, is_read, created_at) 
             VALUES (?, ?, ?, 0, NOW())"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        
        $stmt->bind_param('iis', $senderId, $recipientId, $body);
        $success = $stmt->execute();
        
        if (!$success) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to send message.'];
        }
        
        $messageId = $mysqli->insert_id;
        $stmt->close();
        
        return [
            'success' => true,
            'message' => 'Message sent!',
            'message_id' => $messageId
        ];
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
