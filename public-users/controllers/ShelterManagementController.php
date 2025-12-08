<?php
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/BaseController.php';

SessionManager::init();
// Do not call requireLogin() here (it may redirect HTML); allow fetchData to be requested by the view via AJAX

class ShelterManagementController extends BaseController {
    // Build and return data used by the view or API consumers
    public static function fetchData(): array {
        $userId = SessionManager::getUserId();
        $conn = self::db();

        // Fetch shelter for current user
        $shelter = null;
        $stmt = $conn->prepare("SELECT * FROM shelters WHERE user_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $shelter = $result->fetch_assoc() ?: null;
            $stmt->close();
        }

        // Normalize shelter data
        if ($shelter) {
            $shelter['address'] = $shelter['address'] ?? '';
            $shelter['contact_number'] = $shelter['contact_number'] ?? '';
            $shelter['is_verified'] = isset($shelter['is_verified']) ? (int)$shelter['is_verified'] : 0;
            $shelter['approved'] = isset($shelter['approved']) ? (int)$shelter['approved'] : 0;
            if (!isset($shelter['is_active'])) {
                $shelter['is_active'] = $shelter['is_verified'] ? 1 : 0;
            }
        } else {
            $shelter = [
                'id' => null,
                'shelter_name' => null,
                'address' => '',
                'contact_number' => '',
                'is_verified' => 0,
                'approved' => 0,
                'verified_at' => null,
                'created_at' => null,
                'is_active' => 0,
            ];
        }

        // Prepare stats
        $stats = ['pets' => 0, 'donations' => 0, 'pending_docs' => 0];
        $shelterId = $shelter['id'] ?? null;
        if ($shelterId) {
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM pets WHERE shelter_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $shelterId);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $stats['pets'] = (int)($row['cnt'] ?? 0);
                $stmt->close();
            }

            $stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) as total FROM donations WHERE shelter_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $shelterId);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $stats['donations'] = (float)($row['total'] ?? 0);
                $stmt->close();
            }

            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM shelter_documents WHERE shelter_id = ? AND status = 'pending'");
            if ($stmt) {
                $stmt->bind_param('i', $shelterId);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $stats['pending_docs'] = (int)($row['cnt'] ?? 0);
                $stmt->close();
            }
        }

        // Fetch submitted documents
        $documents = [];
        if ($shelterId) {
            $stmt = $conn->prepare("SELECT id, doc_type, file_path, status, uploaded_at, reviewed_at FROM shelter_documents WHERE shelter_id = ? ORDER BY uploaded_at DESC");
            if ($stmt) {
                $stmt->bind_param('i', $shelterId);
                $stmt->execute();
                $res = $stmt->get_result();
                $documents = $res->fetch_all(MYSQLI_ASSOC) ?: [];
                $stmt->close();
            }
        }

        // Check if documents are all approved
        $allDocsApproved = false;
        if ($shelter && !empty($documents)) {
            $allApproved = true;
            foreach ($documents as $doc) {
                if (strtolower($doc['status']) !== 'approved') {
                    $allApproved = false;
                    break;
                }
            }
            $allDocsApproved = $allApproved;
        }

        // Auto-verify shelter if all documents are approved
        if ($shelter && $allDocsApproved && empty($shelter['is_verified'])) {
            $conn = self::db();
            $updateStmt = $conn->prepare("UPDATE shelters SET is_verified = 1, verified_at = NOW() WHERE id = ?");
            $updateStmt->bind_param('i', $shelter['id']);
            $updateStmt->execute();
            $updateStmt->close();

            // Refresh shelter data
            $shelter['is_verified'] = 1;
            $shelter['verified_at'] = date('Y-m-d H:i:s');
        }

        $requiredTypes = ['dtiregistration','mayors_permit','bir_registration','business_permit','articles_of_incorporation','barangay_clearance','contract_of_lease','other_business_documents'];

        return [
            'shelter' => $shelter,
            'stats' => $stats,
            'documents' => $documents,
            'requiredTypes' => $requiredTypes,
        ];
    }

    public static function handle(): void {
        // require login when rendering server-side view
        SessionManager::requireLogin();
        $data = self::fetchData();
        // extract variables for the view
        $shelter = $data['shelter'];
        $stats = $data['stats'];
        $documents = $data['documents'];
        $requiredTypes = $data['requiredTypes'];

        // Allow the view to render (guard)
        if (!defined('ALLOW_RENDER_SHELTER_MANAGEMENT')) define('ALLOW_RENDER_SHELTER_MANAGEMENT', true);

        include __DIR__ . '/../views/ShelterManagement.php';
    }
}

// If this controller is called directly serve JSON (backend API)
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    // Initialize session and check user without redirect
    SessionManager::init();
    $userId = null;
    try { $userId = SessionManager::getUserId(); } catch (Throwable $e) { $userId = null; }
    if (!$userId) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    $data = ShelterManagementController::fetchData();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}