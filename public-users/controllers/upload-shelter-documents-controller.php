<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Class for possible future use
class UploadShelterDocumentsController extends BaseController {
    // Return the mysqli connection using BaseController's static db()
    public function getDb() {
        return parent::db();
    }
}

// Handle POST request for document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SessionManager::requireLogin();
    $userId = SessionManager::getUserId();
    $shelterId = $_SESSION['shelter_id'] ?? $_SESSION['user']['shelter_id'] ?? null;
    if (!$shelterId) {
        SessionManager::setFlash('error', 'No shelter found for this user.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    // We expect optional per-file doc types via doc_types[]; fallback to global doc_type
    $globalDocType = trim($_POST['doc_type'] ?? '');
    // Allow common Philippine business document types (non-ID)
    $allowedTypes = [
        'dtiregistration',
        'mayors_permit',
        'bir_registration',
        'business_permit',
        'articles_of_incorporation',
        'barangay_clearance',
        'contract_of_lease',
        'other_business_documents'
    ];
    // validate globalDocType only if present
    if ($globalDocType !== '' && !in_array($globalDocType, $allowedTypes)) {
        SessionManager::setFlash('error', 'Invalid document type.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    // Expect documents[] with exactly 3 files
    if (!isset($_FILES['documents']) || !is_array($_FILES['documents']['name'])) {
        SessionManager::setFlash('error', 'No files uploaded.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedExts = ['jpg','jpeg','png','pdf'];
    // Save uploaded documents into storage/documents/ per request
    $uploadDir = __DIR__ . '/../../storage/documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Normalize files array
    $files = [];
    foreach ($_FILES['documents']['name'] as $i => $name) {
        $files[] = [
            'name' => $name,
            'type' => $_FILES['documents']['type'][$i] ?? '',
            'tmp_name' => $_FILES['documents']['tmp_name'][$i] ?? '',
            'error' => $_FILES['documents']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $_FILES['documents']['size'][$i] ?? 0,
        ];
    }

    // Require exactly 3 successfully uploaded files
    $validFiles = array_filter($files, function($f){ return isset($f['tmp_name']) && $f['error'] === UPLOAD_ERR_OK; });
    if (count($validFiles) !== 3) {
        SessionManager::setFlash('error', 'You must upload exactly 3 files.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    // Read per-file doc types if supplied
    $docTypes = is_array($_POST['doc_types']) ? $_POST['doc_types'] : [];

    // Start DB transaction
    $controller = new UploadShelterDocumentsController();
    $mysqli = $controller->getDb();
    $mysqli->begin_transaction();
    $insertStmt = $mysqli->prepare("INSERT INTO shelter_documents (shelter_id, doc_type, file_path, status, uploaded_at) VALUES (?, ?, ?, 'pending', NOW())");
    if (!$insertStmt) {
        $mysqli->rollback();
        SessionManager::setFlash('error', 'Database error.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    $savedAny = false;
    // Reindex validFiles to preserve order and map docTypes
    $validFiles = array_values($validFiles);
    foreach ($validFiles as $idx => $file) {
        $perFileDocType = $docTypes[$idx] ?? $globalDocType;
        $perFileDocType = trim($perFileDocType ?: 'document');
        if (!in_array($perFileDocType, $allowedTypes)) {
            // treat as invalid doc type
            $mysqli->rollback();
            SessionManager::setFlash('error', 'Invalid document type for one of the files.');
            header('Location: ../views/upload_shelter_documents.php');
            exit;
        }
        if ($file['size'] > $maxSize) {
            $mysqli->rollback();
            SessionManager::setFlash('error', 'One of the files exceeds the 5MB limit.');
            header('Location: ../views/upload_shelter_documents.php');
            exit;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            $mysqli->rollback();
            SessionManager::setFlash('error', 'Invalid file type detected. Only JPG, PNG, PDF allowed.');
            header('Location: ../views/upload_shelter_documents.php');
            exit;
        }

    // safe doc type prefix
    $safeDocType = preg_replace('/[^a-z0-9_\-]/i', '_', $perFileDocType ?: 'document');
    $safeName = $safeDocType . '_' . uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $safeName;
    $dbPath = 'storage/documents/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $mysqli->rollback();
            SessionManager::setFlash('error', 'Failed to save one of the uploaded files.');
            header('Location: ../views/upload_shelter_documents.php');
            exit;
        }

    $insertStmt->bind_param('iss', $shelterId, $perFileDocType, $dbPath);
        $ok = $insertStmt->execute();
        if (!$ok) {
            // cleanup file
            @unlink($targetPath);
            $mysqli->rollback();
            SessionManager::setFlash('error', 'Failed to save document info.');
            header('Location: ../views/upload_shelter_documents.php');
            exit;
        }
        $savedAny = true;
    }

    $insertStmt->close();
    $mysqli->commit();

    if ($savedAny) {
        SessionManager::setFlash('success', 'Documents uploaded successfully!');
    } else {
        SessionManager::setFlash('error', 'No files were saved.');
    }

    header('Location: ../views/upload_shelter_documents.php');
    exit;
}
?>