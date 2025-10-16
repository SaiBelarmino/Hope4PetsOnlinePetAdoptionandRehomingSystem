<?php
require_once __DIR__ . '/../../../config/db-connection/db_connection.php';
require_once __DIR__ . '/../../../controllers/BaseController.php';

class IdVerificationRequestsController extends BaseController {
    public static function getVerificationRequests() {
        global $conn;
        
        try {
            // Query to get all verification requests with user details
            $query = "SELECT 
                        v.user_id,
                        CONCAT(u.first_name, ' ', u.last_name) as full_name,
                        u.email,
                        v.id_type,
                        v.id_image_path,
                        v.submission_date,
                        v.status
                    FROM user_verifications v
                    JOIN users u ON v.user_id = u.id
                    ORDER BY 
                        CASE 
                            WHEN v.status = 'pending' THEN 1
                            WHEN v.status = 'approved' THEN 2
                            ELSE 3
                        END,
                        v.submission_date DESC";
            
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $verification_requests = [];
            while ($row = $result->fetch_assoc()) {
                $row['submission_date'] = date('F d, Y h:i A', strtotime($row['submission_date']));
                $verification_requests[] = $row;
            }
            
            return $verification_requests;
            
        } catch (Exception $e) {
            error_log("Error fetching verification requests: " . $e->getMessage());
            return [];
        }
    }
    
    public static function handleVerificationAction($user_id, $action, $reason = '') {
        global $conn;
        
        try {
            if ($action === 'approve') {
                // Update verification status to approved
                $stmt = $conn->prepare("UPDATE user_verifications SET status = 'approved', verified_at = NOW() WHERE user_id = ?");
                $stmt->bind_param('i', $user_id);
                
                if ($stmt->execute()) {
                    // Update user's verified status
                    $updateUser = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
                    $updateUser->bind_param('i', $user_id);
                    $updateUser->execute();
                    
                    return ['status' => 'success'];
                }
            } elseif ($action === 'reject') {
                // Update verification status to rejected
                $stmt = $conn->prepare("UPDATE user_verifications SET status = 'rejected', rejection_reason = ?, rejected_at = NOW() WHERE user_id = ?");
                $stmt->bind_param('si', $reason, $user_id);
                
                if ($stmt->execute()) {
                    return ['status' => 'success'];
                }
            }
            
            return ['status' => 'error', 'message' => 'Failed to process verification action'];
            
        } catch (Exception $e) {
            error_log("Error processing verification action: " . $e->getMessage());
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
    
    echo json_encode(IdVerificationRequestsController::handleVerificationAction($user_id, $action, $reason));
    exit;
}

// Get verification requests for display
$verification_requests = IdVerificationRequestsController::getVerificationRequests();
?>