<?php
// Capture output and ensure JSON on fatal errors
ob_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/BaseController.php';

// Helper class to access protected BaseController::db()
class DBHelper extends BaseController {
    public static function getConn() {
        return parent::db();
    }
}

// Shutdown handler to catch fatal errors and return JSON
register_shutdown_function(function(){
    $err = error_get_last();
    if ($err !== null) {
        // clear any previous output
        @ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Fatal error', 'detail' => $err]);
        exit;
    }
});

SessionManager::init();
// Use getUserId without triggering redirects
$userId = null;
try { $userId = SessionManager::getUserId(); } catch (Throwable $e) { $userId = null; }

// prepare JSON response helper
function send_json($data, $status = 200){
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    // capture any buffered output (warnings/html) and attach for debugging
    $buf = @ob_get_clean();
    if ($buf !== false && strlen(trim($buf)) > 0) {
        $data['debug_output'] = $buf;
    }
    echo json_encode($data);
    exit;
}

if (!$userId) {
    send_json(['success' => false, 'error' => 'Not authenticated'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Invalid request method'], 405);
}

$docId = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;
if (!$docId) {
    send_json(['success' => false, 'error' => 'Missing document id'], 400);
}

try {
    $conn = DBHelper::getConn();

    // Fetch document
    $stmt = $conn->prepare("SELECT id, shelter_id, file_path, status FROM shelter_documents WHERE id = ? LIMIT 1");
    if (!$stmt) throw new Exception('DB prepare failed');
    $stmt->bind_param('i', $docId);
    $stmt->execute();
    $res = $stmt->get_result();
    $doc = $res->fetch_assoc();
    $stmt->close();

    if (!$doc) {
        send_json(['success' => false, 'error' => 'Document not found'], 404);
    }

    // Only allow deleting rejected documents
    if (strtolower($doc['status'] ?? '') !== 'rejected') {
        send_json(['success' => false, 'error' => 'Only rejected documents can be removed', 'status' => $doc['status'] ?? null], 403);
    }

    // Verify shelter ownership
    $stmt = $conn->prepare("SELECT id FROM shelters WHERE user_id = ? LIMIT 1");
    if (!$stmt) throw new Exception('DB prepare failed');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $shelter = $res->fetch_assoc();
    $stmt->close();

    if (!$shelter) {
        send_json(['success' => false, 'error' => 'No shelter found for user'], 403);
    }

    if ((int)$doc['shelter_id'] !== (int)$shelter['id']) {
        send_json(['success' => false, 'error' => 'Unauthorized'], 403);
    }

    // Attempt to remove local file if it exists and is inside project root
    $filePath = trim((string)($doc['file_path'] ?? ''));
    $deletedFile = false;
    if ($filePath !== '') {
        // Resolve project root
        $projectRoot = realpath(__DIR__ . '/../../');
        if ($projectRoot !== false) {
            // If file path is URL, skip physical deletion
            if (!preg_match('/^https?:\/\//i', $filePath)) {
                // Normalize candidate path
                if (substr($filePath, 0, 1) === '/') {
                    $candidate = $projectRoot . $filePath;
                } else {
                    $candidate = $projectRoot . '/' . ltrim($filePath, '/');
                }
                $real = realpath($candidate);
                if ($real && strpos($real, $projectRoot) === 0 && is_file($real)) {
                    // try delete
                    if (@unlink($real)) {
                        $deletedFile = true;
                    } else {
                        // capture inability to unlink
                        error_log('DeleteDocumentController: unlink failed for ' . $real);
                    }
                } else {
                    // file not found or outside project
                    error_log('DeleteDocumentController: file not eligible for deletion: ' . $candidate);
                }
            }
        }
    }

    // Delete DB record
    $stmt = $conn->prepare("DELETE FROM shelter_documents WHERE id = ? LIMIT 1");
    if (!$stmt) throw new Exception('DB prepare failed');
    $stmt->bind_param('i', $docId);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if (!$ok || $affected <= 0) {
        send_json(['success' => false, 'error' => 'Failed to remove document record'], 500);
    }

    send_json(['success' => true, 'deleted_file' => $deletedFile]);

} catch (Throwable $ex) {
    error_log('DeleteDocumentController error: ' . $ex->getMessage());
    send_json(['success' => false, 'error' => 'Server error', 'detail' => $ex->getMessage()], 500);
}
