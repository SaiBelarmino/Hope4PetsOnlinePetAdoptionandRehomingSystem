<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controllers/messages-controller.php';

$authUserId = 0;
if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $authUserId = (int)$_SESSION['user']['id'];
} elseif (isset($_SESSION['user_id'])) {
    $authUserId = (int)$_SESSION['user_id'];
} elseif (isset($_SESSION['user']) && is_numeric($_SESSION['user'])) {
    $authUserId = (int)$_SESSION['user'];
}

if ($authUserId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$other = isset($_GET['u']) ? (int)$_GET['u'] : 0;
$afterId = isset($_GET['after_id']) ? (int)$_GET['after_id'] : 0;

if ($other <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing target user id']);
    exit;
}

if (class_exists('MessagesController')) {
    $msgs = MessagesController::getMessagesBetweenUsers($authUserId, $other, 200);
} else {
    $msgs = PublicMessagesController::conversation($authUserId, $other, 200);
}

if ($afterId > 0) {
    $filtered = [];
    foreach ($msgs as $m) {
        if (isset($m['id']) && (int)$m['id'] > $afterId) $filtered[] = $m;
    }
    $msgs = $filtered;
}

echo json_encode(['success' => true, 'messages' => $msgs]);
exit;
