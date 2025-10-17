<?php
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../../config/db-connection/db_connection.php';

header('Content-Type: application/json');
$session = new SessionManager();
$user = $session->getUser();
$userId = $user['id'] ?? 0;
if (!$userId) {
    echo json_encode(['ok' => false, 'is_verified' => 0]);
    exit;
}

try {
    global $conn;
    if (!($conn instanceof mysqli)) {
        throw new Exception('DB connection unavailable');
    }
    $stmt = $conn->prepare('SELECT id, is_verified FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) throw new Exception('Failed to prepare statement');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    $isVerified = (int)($row['is_verified'] ?? 0);
    echo json_encode(['ok' => true, 'user_id' => $userId, 'is_verified' => $isVerified]);
} catch (Exception $e) {
    error_log('check_verification error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'is_verified' => 0]);
}
