<?php

require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/BaseController.php';

if (class_exists('SessionManager') && method_exists('SessionManager','init')) {
    try { SessionManager::init(); } catch (Exception $e) { if (session_status() === PHP_SESSION_NONE) { session_start(); } }
} else {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

// Helper to set flash messages using SessionManager if available, else $_SESSION
function set_flash($message, $type = 'info') {
    if (class_exists('SessionManager') && method_exists('SessionManager','setFlash')) {
        try { SessionManager::setFlash($type, $message); return; } catch(Exception $e) { /* fallback */ }
    }
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

class ReportPostController extends BaseController
{
    public static function createReport(int $reporterId, int $postId, string $reason): array
    {
        $inserted = false;
        $error = null;

        try {
            $db = self::db(); // Assumes BaseController::db() returns a mysqli connection
            $stmt = $db->prepare(
                "INSERT INTO post_reports (reporter_id, post_id, reason, status) 
                 VALUES (?, ?, ?, 'open')"
            );
            if (!$stmt) {
                $error = 'Prepare failed: ' . $db->error;
                return [$inserted, $error];
            }

            $stmt->bind_param('iis', $reporterId, $postId, $reason);

            if ($stmt->execute()) {
                $inserted = true;
            } else {
                // Check for duplicate entry (unique constraint violation)
                if ($db->errno === 1062) {
                    $error = 'You have already reported this post.';
                } else {
                    $error = 'Execute failed: ' . $stmt->error;
                }
            }
            $stmt->close();
        } catch (Throwable $t) {
            error_log("ReportPostController Error: " . $t->getMessage());
            $error = 'An unexpected error occurred. Please try again later.';
        }

        return [$inserted, $error];
    }
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('Invalid request method.', 'warning');
    header('Location: ../views/index.php');
    exit;
}

// Check if user is logged in
$reporterId = $_SESSION['user']['id'] ?? null;
if (!$reporterId) {
    set_flash('You must be logged in to report a post.', 'danger');
    header('Location: ../login.php');
    exit;
}

// Collect and validate input
$postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
$details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

if (!$postId || !$reason) {
    set_flash('Post ID and reason are required to submit a report.', 'danger');
    header('Location: ../views/index.php');
    exit;
}

// Prepare data
$fullReason = $reason;
if (!empty($details)) {
    $fullReason .= ': ' . $details;
}
// Truncate if necessary to fit in the database column
$fullReason = substr($fullReason, 0, 255);

// Attempt to create the report
list($inserted, $dbError) = ReportPostController::createReport($reporterId, $postId, $fullReason);

if ($inserted) {
    set_flash('Post reported successfully. Our team will review it shortly.', 'success');
} else {
    set_flash($dbError ?: 'Failed to submit the report.', 'danger');
}

header('Location: ../views/index.php');
exit;