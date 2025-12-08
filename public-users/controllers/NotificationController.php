<?php

class NotificationController
{
    private $pdo;

    public function __construct()
    {
        // Update credentials if needed
        $this->pdo = new PDO('mysql:host=localhost;dbname=hope4pets', 'root', '');
    }

    public function getRecentNotifications(int $limit = 5): array
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return [];

        // Use intval to ensure $limit is an integer and prevent SQL injection
        $limit = intval($limit);

        $stmt = $this->pdo->prepare("SELECT id, message, icon, bg, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format time for display
        foreach ($notifications as &$n) {
            $n['time'] = $this->formatTimeAgo($n['created_at']);
        }
        return $notifications;
    }

    public function getAllNotifications(): array
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return [];

        $stmt = $this->pdo->prepare("SELECT id, message, icon, bg, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notifications as &$n) {
            $n['time'] = $this->formatTimeAgo($n['created_at']);
        }
        return $notifications;
    }

    private function formatTimeAgo($datetime)
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        if ($diff < 60) return $diff . ' sec ago';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hour ago';
        return date('M d, Y', $timestamp);
    }
}