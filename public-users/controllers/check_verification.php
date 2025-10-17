<?php
// Returns JSON with current user's fresh verification status.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/db-connection/db_connection.php';

$userId = (int)($_SESSION['user']['id'] ?? 0);
if ($userId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'not_logged_in']);
    exit;
}

$stmt = $conn->prepare("SELECT is_verified FROM users WHERE id = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['ok' => false, 'message' => 'db_error']);
    exit;
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

$isVerified = !empty($row['is_verified']) ? 1 : 0;
// Update session copy so server-rendered pages and JS can reflect it immediately
if (!isset($_SESSION['user'])) $_SESSION['user'] = [];
$_SESSION['user']['is_verified'] = $isVerified;

echo json_encode(['ok' => true, 'is_verified' => $isVerified]);
exit;

?>
