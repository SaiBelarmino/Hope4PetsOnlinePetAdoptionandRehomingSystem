<?php
// public-users/api/get_post_comments.php
// Returns all comments for a given post as JSON

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../config/db-connection/db_connection.php';

header('Content-Type: application/json');

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
if ($postId <= 0) {
    echo json_encode([]);
    exit;
}



$comments = [];
if (isset($conn) && $conn instanceof mysqli) {
    $sql = "SELECT c.content, c.created_at, u.full_name FROM post_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'content' => htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8'),
            'created_at' => date('M d, Y H:i', strtotime($row['created_at'])),
            'full_name' => htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8'),
        ];
    }
    $stmt->close();
}
echo json_encode($comments);
