<?php
// api/heart_post.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db-connection/db_connection.php';
$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$postId = $input['post_id'] ?? null;
$action = $input['action'] ?? null;
$reactionType = 'heart';
if (!$postId || !in_array($action, ['heart', 'unheart'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}
$conn = $GLOBALS['db_connection'] ?? null;
if (!$conn) {
    echo json_encode(['error' => 'DB connection error']);
    exit;
}
if ($action === 'heart') {
    // Insert or ignore
    $stmt = $conn->prepare('INSERT IGNORE INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $postId, $userId, $reactionType);
    $stmt->execute();
    $stmt->close();
} else {
    // Remove heart
    $stmt = $conn->prepare('DELETE FROM post_reactions WHERE post_id = ? AND user_id = ? AND reaction_type = ?');
    $stmt->bind_param('iis', $postId, $userId, $reactionType);
    $stmt->execute();
    $stmt->close();
}
// Get new count
$stmt = $conn->prepare('SELECT COUNT(*) FROM post_reactions WHERE post_id = ? AND reaction_type = ?');
$stmt->bind_param('is', $postId, $reactionType);
$stmt->execute();
$stmt->bind_result($heartCount);
$stmt->fetch();
$stmt->close();
// Check if user hearted
$stmt = $conn->prepare('SELECT 1 FROM post_reactions WHERE post_id = ? AND user_id = ? AND reaction_type = ?');
$stmt->bind_param('iis', $postId, $userId, $reactionType);
$stmt->execute();
$userHearted = $stmt->get_result()->num_rows > 0;
$stmt->close();
echo json_encode(['success' => true, 'heart_count' => $heartCount, 'user_hearted' => $userHearted]);
