<?php
// Upload handler for shelter documents
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/BaseController.php';

SessionManager::init();
SessionManager::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../controllers/ShelterManagementController.php');
    exit;
}

// Basic CSRF check if token provided
$token = $_POST['csrf_token'] ?? '';
if (!empty($token) && !\SessionManager::verifyCSRFToken($token)) {
    SessionManager::setFlash('danger', 'Invalid CSRF token.');
    header('Location: ../controllers/ShelterManagementController.php');
    exit;
}

$userId = SessionManager::getUserId();
// Use the global DB connection instead of calling protected BaseController::db()
require_once __DIR__ . '/../../config/db-connection/db_connection.php';
global $conn;
if (!isset($conn) || !($conn instanceof mysqli)) {
    SessionManager::setFlash('danger', 'Database connection not initialized.');
    header('Location: ../controllers/ShelterManagementController.php');
    exit;
}

// Ensure shelter belongs to user — include shelter_name
$stmt = $conn->prepare('SELECT id, shelter_name FROM shelters WHERE user_id = ? LIMIT 1');
if (!$stmt) {
    SessionManager::setFlash('danger', 'Internal error. Please try again.');
    header('Location: ../controllers/ShelterManagementController.php');
    exit;
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$shelter = $res->fetch_assoc();
$stmt->close();

if (empty($shelter['id'])) {
    SessionManager::setFlash('danger', 'No shelter found for your account.');
    header('Location: ../controllers/ShelterManagementController.php');
    exit;
}
$shelterId = (int)$shelter['id'];
// use shelter_name from DB as folder name (preserve DB format), but sanitize dangerous chars
$shelterDir = trim($shelter['shelter_name'] ?? 'shelter_'.$shelterId);
if ($shelterDir === '') $shelterDir = 'shelter_'.$shelterId;
// replace path separators and null bytes to avoid directory traversal issues
$shelterDir = str_replace(array('/', '\\', "\0"), '_', $shelterDir);

// Save files to project storage directory using shelter name from DB
$uploadDir = __DIR__ . '/../../storage/documents/' . $shelterDir;
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
// ensure writable
if (!is_writable($uploadDir)) {
    @chmod($uploadDir, 0755);
    if (!is_writable($uploadDir)) {
        // try more permissive on Windows/XAMPP or other environments
        @chmod($uploadDir, 0777);
    }
}
if (!is_writable($uploadDir)) {
    error_log("UploadDocumentsController: upload directory not writable: $uploadDir");
}

// Allowed mime types and max file size (respect PHP limits)
$allowedMime = ['image/jpeg','image/png','application/pdf'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Log PHP upload limits for debugging
error_log('UploadDocumentsController: upload_max_filesize=' . ini_get('upload_max_filesize') . ' post_max_size=' . ini_get('post_max_size'));

$uploaded = 0;
$errors = [];

// Process individual file fields for required documents
// Only error if all files are missing, otherwise process what is present
$docFields = [
    'barangay_permit' => 'Barangay Permit',
    'barangay_clearance' => 'Barangay Clearance',
    'bir_permit' => 'BIR Permit',
    'bai_permit' => 'Bureau of Animal Industry Permit'
];
$anyFileSelected = false;
foreach ($docFields as $field => $label) {
    if (!empty($_FILES[$field]) && !empty($_FILES[$field]['name'])) {
        $anyFileSelected = true;
        $file = $_FILES[$field];
        $errorCode = $file['error'] ?? UPLOAD_ERR_OK;
        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = "$label: upload error code $errorCode";
            continue;
        }
        $tmp = $file['tmp_name'] ?? null;
        if (empty($tmp) || !is_uploaded_file($tmp)) {
            $errors[] = "$label: file upload failed (tmp missing).";
            continue;
        }
        $size = $file['size'] ?? 0;
        $type = mime_content_type($tmp) ?: '';
        if ($size > $maxSize) { $errors[] = "$label: file is too large."; continue; }
        if (!in_array($type, $allowedMime)) { $errors[] = "$label: invalid file type ($type)."; continue; }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/','_', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = time() . '_' . bin2hex(random_bytes(6)) . '_' . $safeName . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($tmp, $dest)) {
            $errors[] = "$label: failed to save file.";
            continue;
        }
        $relativePath = '/storage/documents/' . $shelterDir . '/' . $filename;
        $stmt = $conn->prepare('INSERT INTO shelter_documents (shelter_id, doc_type, file_path, status, uploaded_at) VALUES (?, ?, ?, ?, NOW())');
        if ($stmt) {
            $status = 'pending';
            $stmt->bind_param('isss', $shelterId, $label, $relativePath, $status);
            $stmt->execute();
            $stmt->close();
        }
        $uploaded++;
    }
}
if (!$anyFileSelected) {
    $errors[] = 'No files selected for upload.';
}

// Process optional document
$optionalType = trim($_POST['optional_doc_type'] ?? '');
if (!empty($_FILES['optional_document']) && !empty($_FILES['optional_document']['name'])) {
    $errorCode = $_FILES['optional_document']['error'] ?? UPLOAD_ERR_OK;
    if ($errorCode !== UPLOAD_ERR_OK) {
        $errors[] = "Optional: upload error code $errorCode";
        error_log("UploadDocumentsController: optional upload error code $errorCode");
    } else {
        $origName = $_FILES['optional_document']['name'];
        $tmp = $_FILES['optional_document']['tmp_name'];
        if (empty($tmp) || !is_uploaded_file($tmp)) {
            $errors[] = "Optional: file upload failed (tmp missing).";
            error_log('UploadDocumentsController: optional tmp missing: '.print_r($_FILES['optional_document'],true));
        } else {
            $size = $_FILES['optional_document']['size'];
            $type = mime_content_type($tmp) ?: '';

            if ($size > $maxSize) {
                $errors[] = "Optional: file is too large.";
            } elseif (!in_array($type, $allowedMime)) {
                $errors[] = "Optional: invalid file type ($type).";
            } else {
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/','_', pathinfo($origName, PATHINFO_FILENAME));
                $filename = time() . '_' . bin2hex(random_bytes(6)) . '_' . $safeName . '.' . $ext;
                $dest = $uploadDir . '/' . $filename;

                if (!move_uploaded_file($tmp, $dest)) {
                    $errors[] = "Optional: failed to save file.";
                    error_log("UploadDocumentsController: move_uploaded_file failed for optional from $tmp to $dest; is_writable=".(is_writable($uploadDir)?'yes':'no'));
                    error_log('$_FILES optional: '.print_r($_FILES['optional_document'],true));
                } else {
                    $relativePath = '/storage/documents/' . $shelterDir . '/' . $filename;
                    $docType = $optionalType ?: 'other_business_documents';
                    $stmt = $conn->prepare('INSERT INTO shelter_documents (shelter_id, doc_type, file_path, status, uploaded_at) VALUES (?, ?, ?, ?, NOW())');
                    if ($stmt) {
                        $status = 'pending';
                        $stmt->bind_param('isss', $shelterId, $docType, $relativePath, $status);
                        $stmt->execute();
                        if ($stmt->errno) {
                            error_log('UploadDocumentsController: DB insert error (optional): ' . $stmt->error);
                        }
                        $stmt->close();
                    } else {
                        error_log('UploadDocumentsController: DB prepare error (optional): ' . $conn->error);
                    }
                    $uploaded++;
                }
            }
        }
    }
}

// Detect AJAX request
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    if ($isAjax) {
        $debug = [
            'POST' => $_POST,
            'FILES' => $_FILES,
            'uploaded' => $uploaded,
            'errors' => $errors,
            'uploadDir' => $uploadDir,
            'isWritable' => is_writable($uploadDir)
        ];
        if ($uploaded > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Uploaded $uploaded file(s) successfully.",
                'errors' => $errors,
                'debug' => $debug
            ]);
        } else {
            // Silent failure: do not send any message
            echo json_encode([
                'success' => false,
                'message' => '',
                'errors' => $errors,
                'debug' => $debug
            ]);
        }
        exit;
    } else {
        if ($uploaded > 0) {
            SessionManager::setFlash('success', "Uploaded $uploaded file(s) successfully.");
        } else {
            $msg = 'Upload failed.';
            if (!empty($errors)) $msg .= ' ' . implode('; ', $errors);
            SessionManager::setFlash('danger', $msg);
        }
    }
// --- SHELTER DOCUMENT UPLOAD FUNCTION ---
/**
 * Upload shelter documents (Barangay Permit, Barangay Clearance, BIR Permit, BAI Permit)
 * @param mysqli $conn
 * @param int $shelterId
 * @param string $shelterDir
 * @param array $files $_FILES array
 * @return array [uploaded_count, errors]
 */
function uploadShelterDocuments($conn, $shelterId, $shelterDir, $files) {
    $uploadDir = __DIR__ . '/../../storage/documents/' . $shelterDir;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (!is_writable($uploadDir)) {
        @chmod($uploadDir, 0755);
        if (!is_writable($uploadDir)) @chmod($uploadDir, 0777);
    }
    $allowedMime = ['image/jpeg','image/png','application/pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $docFields = [
        'barangay_permit' => 'Barangay Permit',
        'barangay_clearance' => 'Barangay Clearance',
        'bir_permit' => 'BIR Permit',
        'bai_permit' => 'Bureau of Animal Industry Permit'
    ];
    $uploaded = 0;
    $errors = [];
    foreach ($docFields as $field => $label) {
        if (empty($files[$field]) || empty($files[$field]['name'])) {
            $errors[] = "$label: No file uploaded.";
            continue;
        }
        $file = $files[$field];
        $errorCode = $file['error'] ?? UPLOAD_ERR_OK;
        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = "$label: upload error code $errorCode";
            continue;
        }
        $tmp = $file['tmp_name'] ?? null;
        if (empty($tmp) || !is_uploaded_file($tmp)) {
            $errors[] = "$label: file upload failed (tmp missing).";
            continue;
        }
        $size = $file['size'] ?? 0;
        $type = mime_content_type($tmp) ?: '';
        if ($size > $maxSize) { $errors[] = "$label: file is too large."; continue; }
        if (!in_array($type, $allowedMime)) { $errors[] = "$label: invalid file type ($type)."; continue; }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/','_', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = time() . '_' . bin2hex(random_bytes(6)) . '_' . $safeName . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($tmp, $dest)) {
            $errors[] = "$label: failed to save file.";
            continue;
        }
        $relativePath = '/storage/documents/' . $shelterDir . '/' . $filename;
        $stmt = $conn->prepare('INSERT INTO shelter_documents (shelter_id, doc_type, file_path, status, uploaded_at) VALUES (?, ?, ?, ?, NOW())');
        if ($stmt) {
            $status = 'pending';
            $stmt->bind_param('isss', $shelterId, $label, $relativePath, $status);
            $stmt->execute();
            $stmt->close();
        }
        $uploaded++;
    }
    return ['uploaded_count' => $uploaded, 'errors' => $errors];
}

// --- ADMIN REVIEW FUNCTIONS ---
// Get all pending documents for admin review
function getPendingDocuments($conn) {
    $docs = [];
    $sql = "SELECT d.id, d.shelter_id, d.doc_type, d.file_path, d.status, d.uploaded_at, s.shelter_name FROM shelter_documents d JOIN shelters s ON d.shelter_id = s.id WHERE d.status = 'pending' ORDER BY d.uploaded_at DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $docs[] = $row;
        }
    }
    return $docs;
}

// Approve or decline a document
function reviewDocument($conn, $docId, $action, $adminId) {
    $status = ($action === 'approve') ? 'approved' : 'declined';
    $stmt = $conn->prepare("UPDATE shelter_documents SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('sii', $status, $adminId, $docId);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    return false;
}

// --- END ADMIN REVIEW FUNCTIONS ---

header('Location: ../views/ShelterManagement.php');
exit;
