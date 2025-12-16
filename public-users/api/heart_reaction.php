<?php
// heart_reaction.php
// Handles AJAX heart (like) reaction for posts
require_once __DIR__ . '/../controllers/index-controller.php';
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
header('Content-Type: application/json');
$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    echo json_encode(['success'=>false, 'error'=>'Not logged in']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
$liked = !empty($data['liked']);
if (!$postId) {
    echo json_encode(['success'=>false, 'error'=>'Invalid post id']);
    exit;
}
// DB connection
require_once __DIR__ . '/../../config/db-connection/db_connection.php';
$conn = $GLOBALS['conn'] ?? null;
if (!$conn) { echo json_encode(['success'=>false, 'error'=>'DB error']); exit; }
// Check if already reacted
$stmt = $conn->prepare('SELECT id FROM post_reactions WHERE post_id=? AND user_id=?');
$stmt->bind_param('ii', $postId, $userId);
$stmt->execute();
$stmt->store_result();
$already = $stmt->num_rows > 0;
$stmt->free_result();
if ($liked && !$already) {
    $stmt = $conn->prepare('INSERT INTO post_reactions (post_id, user_id, created_at) VALUES (?, ?, NOW())');
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
} elseif (!$liked && $already) {
    $stmt = $conn->prepare('DELETE FROM post_reactions WHERE post_id=? AND user_id=?');
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
}
// Get new count
$stmt = $conn->prepare('SELECT COUNT(*) FROM post_reactions WHERE post_id=?');
$stmt->bind_param('i', $postId);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

// Update the posts table with the new reaction count
$stmt = $conn->prepare('UPDATE posts SET reaction_count = ? WHERE id = ?');
$stmt->bind_param('ii', $count, $postId);
$stmt->execute();
$stmt->close();

// Check if user has hearted after the update (always reflect DB state)
$stmt = $conn->prepare('SELECT 1 FROM post_reactions WHERE post_id=? AND user_id=?');
$stmt->bind_param('ii', $postId, $userId);
$stmt->execute();
$stmt->store_result();
$dbHearted = $stmt->num_rows > 0;
$stmt->free_result();
$stmt->close();

// Return new state
$res = [
    'success' => true,
    'count' => (int)$count,
    'liked' => $dbHearted
];
echo json_encode($res);