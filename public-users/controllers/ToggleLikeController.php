<?php
// filepath: public-users/controllers/ToggleLikeController.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

SessionManager::init();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    if (empty($_SESSION['user']['id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    // Expecting post_id from frontend; if your reactions are for pets, map to pet_id accordingly
    $postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
    if ($postId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid id']);
        exit;
    }

    // Like-only
    $reactionType = 'like';

    $db = BaseController::getConnection();
    $userId = (int)$_SESSION['user']['id'];

    // Toggle like in pet_reactions using pet_id = $postId (adjust if needed)
    $stmt = $db->prepare('SELECT id FROM pet_reactions WHERE pet_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$postId, $userId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Unlike
        $del = $db->prepare('DELETE FROM pet_reactions WHERE id = ?');
        $del->execute([$existing['id']]);
        $liked = false;
    } else {
        // Like
        $ins = $db->prepare('INSERT INTO pet_reactions (pet_id, user_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())');
        $ins->execute([$postId, $userId, $reactionType]);
        $liked = true;
    }

    $cntStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM pet_reactions WHERE pet_id = ?');
    $cntStmt->execute([$postId]);
    $count = (int)$cntStmt->fetchColumn();

    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
