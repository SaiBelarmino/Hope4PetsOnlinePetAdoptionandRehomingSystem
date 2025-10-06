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

$recipient = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : (isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0);
$body = isset($_POST['body']) ? trim($_POST['body']) : (isset($_GET['body']) ? trim($_GET['body']) : '');

if ($recipient <= 0 || $body === '') {
    echo json_encode(['success' => false, 'message' => 'Missing recipient or empty message']);
    exit;
}

if (class_exists('MessagesController')) {
    $res = MessagesController::sendMessage($authUserId, $recipient, $body);
    if (isset($res['success']) && $res['success']) {
        $lastId = (int)($res['message_id'] ?? 0);
        if ($lastId > 0) {
            $row = MessagesController::getMessagesBetweenUsers($authUserId, $recipient, 200);
            $found = [];
            foreach ($row as $r) { if ((int)$r['id'] === $lastId) { $found = $r; break; } }
            echo json_encode(['success' => true, 'message' => $found ?: ['id' => $lastId, 'body' => $body]]);
            exit;
        }
        echo json_encode(['success' => true, 'message' => ['body' => $body]]);
        exit;
    }
    echo json_encode($res);
    exit;
} else {
    $row = PublicMessagesController::send($authUserId, $recipient, $body);
    if ($row) {
        echo json_encode(['success' => true, 'message' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
    exit;
}
