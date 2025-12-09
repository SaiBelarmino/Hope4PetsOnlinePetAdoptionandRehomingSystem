<?php

class NotificationController
{
    private $pdo;

    public function __construct()
    {
        // Update credentials if needed
        $this->pdo = new PDO('mysql:host=localhost;dbname=hope4pets', 'root', '');
    }

    public function getRecentNotifications(int $limit = 50): array
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return [];

        $limit = intval($limit);
        try {
            $stmt = $this->pdo->prepare("SELECT id, message, url, icon, bg, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Table missing or other DB error
            return [];
        }
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

        try {
            $stmt = $this->pdo->prepare("SELECT id, message, url, icon, bg, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
        foreach ($notifications as &$n) {
            $n['time'] = $this->formatTimeAgo($n['created_at']);
        }
        return $notifications;
    }

    /**
     * Notify all users except the actor.
     * @param string $message
     * @param int $actorId
     * @param string $icon
     * @param string $bg
     * @param string|null $url
     */
    public function notifyAllExceptActor(string $message, int $actorId, string $icon = 'ti-bell', string $bg = 'bg-light-info text-info', string $url = null)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id != ?");
        $stmt->execute([$actorId]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$userIds) return;

        $insert = $this->pdo->prepare("INSERT INTO notifications (user_id, message, url, icon, bg) VALUES (?, ?, ?, ?, ?)");
        foreach ($userIds as $userId) {
            $insert->execute([$userId, $message, $url, $icon, $bg]);
        }
    }

    public function deleteNotification(int $notificationId): bool
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return false;
        try {
            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            return $stmt->execute([$notificationId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_notification') {
    session_start();
    $controller = new NotificationController();
    $id = (int)($_POST['id'] ?? 0);
    $success = $controller->deleteNotification($id);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}