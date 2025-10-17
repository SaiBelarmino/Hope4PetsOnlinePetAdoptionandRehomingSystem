<?php
require_once __DIR__ . '/../../../config/db-connection/db_connection.php';
require_once __DIR__ . '/../../../config/SessionManager.php';

class IdVerificationRequestsController {
    public static function getVerificationRequests() {
        global $conn;

        try {
            // Select recent documents (including the user info). We show each document row.
            $sql = "SELECT ud.id AS doc_id, ud.user_id, ud.doc_type, ud.file_path, ud.status, ud.uploaded_at, u.full_name, u.email, u.profile_photo
                    FROM user_documents ud
                    JOIN users u ON ud.user_id = u.id
                    ORDER BY 
                        CASE WHEN ud.status = 'pending' THEN 1 WHEN ud.status = 'approved' THEN 2 ELSE 3 END,
                        ud.uploaded_at DESC";

            $result = $conn->query($sql);
            if (!$result) throw new Exception('Query failed: ' . $conn->error);

            $verification_requests = [];
            while ($row = $result->fetch_assoc()) {
                $row['uploaded_at'] = date('F d, Y h:i A', strtotime($row['uploaded_at']));
                $verification_requests[] = $row;
            }
            return $verification_requests;
        } catch (Exception $e) {
            error_log('Error fetching verification requests: ' . $e->getMessage());
            return [];
        }
    }

    public static function handleVerificationAction($user_id, $action, $reason = '', $doc_id = null) {
        global $conn;
        try {
            // Admin id if available
            $adminId = null;
            if (class_exists('AdminSessionManager')) {
                $adminId = AdminSessionManager::getAdminId();
            }

            if ($action === 'approve') {
                if ($doc_id) {
                    // Approve specific document
                    $stmt = $conn->prepare("UPDATE user_documents SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ? AND status = 'pending'");
                    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
                    $stmt->bind_param('ii', $adminId, $doc_id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    if ($ok) {
                        // Get user id for this doc
                        $row = $conn->query("SELECT user_id FROM user_documents WHERE id = " . intval($doc_id))->fetch_assoc();
                        $uid = $row['user_id'] ?? $user_id;
                        $u = $conn->prepare('UPDATE users SET is_verified = 1 WHERE id = ?');
                        if ($u) {
                            $u->bind_param('i', $uid);
                            $u->execute();
                            $u->close();
                        }
                        return ['status' => 'success', 'doc_id' => $doc_id, 'user_id' => $uid];
                    }
                } else {
                    // Approve all pending documents for this user and mark reviewed
                    $stmt = $conn->prepare("UPDATE user_documents SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE user_id = ? AND status = 'pending'");
                    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
                    $stmt->bind_param('ii', $adminId, $user_id);
                    $ok = $stmt->execute();
                    $stmt->close();

                    if ($ok) {
                        // Mark the user as verified
                        $u = $conn->prepare('UPDATE users SET is_verified = 1 WHERE id = ?');
                        if ($u) {
                            $u->bind_param('i', $user_id);
                            $u->execute();
                            $u->close();
                        }
                        return ['status' => 'success', 'user_id' => $user_id];
                    }
                }
            } elseif ($action === 'reject') {
                if ($doc_id) {
                    // Reject specific document
                    $stmt = $conn->prepare("UPDATE user_documents SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ? AND status = 'pending'");
                    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
                    $stmt->bind_param('ii', $adminId, $doc_id);
                    $ok = $stmt->execute();
                    $stmt->close();
                    if ($ok) {
                        // Optionally ensure user remains unverified if no approved docs remain
                        $row = $conn->query("SELECT user_id FROM user_documents WHERE id = " . intval($doc_id))->fetch_assoc();
                        $uid = $row['user_id'] ?? $user_id;
                        // If user has any approved docs, keep verified; otherwise set to 0
                        $cnt = (int)($conn->query("SELECT COUNT(*) AS c FROM user_documents WHERE user_id = " . intval($uid) . " AND status = 'approved'")->fetch_assoc()['c'] ?? 0);
                        if ($cnt === 0) {
                            $u = $conn->prepare('UPDATE users SET is_verified = 0 WHERE id = ?');
                            if ($u) {
                                $u->bind_param('i', $uid);
                                $u->execute();
                                $u->close();
                            }
                        }
                        return ['status' => 'success', 'doc_id' => $doc_id, 'user_id' => $uid];
                    }
                } else {
                    // Reject all pending documents for user
                    $stmt = $conn->prepare("UPDATE user_documents SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE user_id = ? AND status = 'pending'");
                    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
                    $stmt->bind_param('ii', $adminId, $user_id);
                    $ok = $stmt->execute();
                    $stmt->close();

                    if ($ok) {
                        // Optionally set user's is_verified = 0
                        $u = $conn->prepare('UPDATE users SET is_verified = 0 WHERE id = ?');
                        if ($u) {
                            $u->bind_param('i', $user_id);
                            $u->execute();
                            $u->close();
                        }
                        // We don't persist reason in schema; admin can leave manual notes elsewhere.
                        return ['status' => 'success', 'user_id' => $user_id];
                    }
                }
            }

            return ['status' => 'error', 'message' => 'Failed to process verification action'];
        } catch (Exception $e) {
            error_log('Error processing verification action: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error occurred'];
        }
    }
}

// Handle POST requests for approving/rejecting verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $doc_id = isset($_POST['doc_id']) ? (int)$_POST['doc_id'] : null;
    echo json_encode(IdVerificationRequestsController::handleVerificationAction($user_id, $action, $reason, $doc_id));
    exit;
}

// Get verification requests for display
$verification_requests = IdVerificationRequestsController::getVerificationRequests();
?>