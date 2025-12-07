<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../controllers/User/MessageThreadsController.php';

SessionManager::init();
// Optional: Add admin session check if this should be a protected endpoint
// AdminSessionManager::requireAdminLogin();

try {
    $threads = MessageThreadsController::getMessageThreads();
    echo json_encode($threads);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching message threads.', 'details' => $e->getMessage()]);
}