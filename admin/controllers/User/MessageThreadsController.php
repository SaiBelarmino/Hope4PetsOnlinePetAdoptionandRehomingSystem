<?php

require_once __DIR__ . '/../../../controllers/BaseController.php';

class MessageThreadsController extends BaseController
{
    /**
     * Fetches all message threads. A thread is a conversation between two users.
     */
    public static function getMessageThreads(): array
    {
        // This query groups messages into threads based on the participants.
        // LEAST and GREATEST are used to ensure the pair (user1, user2) is treated the same as (user2, user1).
        $sql = "
            SELECT 
                LEAST(m.sender_id, m.recipient_id) AS user1_id,
                GREATEST(m.sender_id, m.recipient_id) AS user2_id,
                MAX(m.created_at) AS updated_at,
                (SELECT body FROM messages WHERE id = MAX(m.id) ORDER BY created_at DESC LIMIT 1) as last_message,
                SUM(CASE WHEN m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
            FROM messages m
            WHERE m.sender_id IS NOT NULL AND m.recipient_id IS NOT NULL
            GROUP BY user1_id, user2_id
            ORDER BY updated_at DESC
        ";

        $threads = self::fetchAll($sql);

        // For each thread, fetch the participant details.
        foreach ($threads as &$thread) {
            $user1_sql = "SELECT id, full_name, profile_photo FROM users WHERE id = ?";
            $thread['user1'] = self::fetchOne($user1_sql, 'i', [$thread['user1_id']]);

            $user2_sql = "SELECT id, full_name, profile_photo FROM users WHERE id = ?";
            $thread['user2'] = self::fetchOne($user2_sql, 'i', [$thread['user2_id']]);
        }

        return $threads;
    }
}