<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

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

    $docType = trim($_POST['doc_type'] ?? '');
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
    if (!in_array($docType, $allowedTypes)) {
        SessionManager::setFlash('error', 'Invalid document type.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        SessionManager::setFlash('error', 'No file uploaded or upload error.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    $file = $_FILES['document'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedExts = ['jpg','jpeg','png','pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        SessionManager::setFlash('error', 'Invalid file type. Only JPG, PNG, PDF allowed.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }
    if ($file['size'] > $maxSize) {
        SessionManager::setFlash('error', 'File too large. Max 5MB allowed.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    $uploadDir = __DIR__ . '/../../storage/uploads/shelter_docs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $safeName = uniqid('shelterdoc_') . '.' . $ext;
    $targetPath = $uploadDir . $safeName;
    $dbPath = 'storage/uploads/shelter_docs/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        SessionManager::setFlash('error', 'Failed to save uploaded file.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }

    // Insert into DB
    $mysqli = BaseController::db();
    $stmt = $mysqli->prepare("INSERT INTO shelter_documents (shelter_id, doc_type, file_path, status, uploaded_at) VALUES (?, ?, ?, 'pending', NOW())");
    if (!$stmt) {
        SessionManager::setFlash('error', 'Database error.');
        header('Location: ../views/upload_shelter_documents.php');
        exit;
    }
    $stmt->bind_param('iss', $shelterId, $docType, $dbPath);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        SessionManager::setFlash('success', 'Document uploaded successfully!');
    } else {
        SessionManager::setFlash('error', 'Failed to save document info.');
    }
    header('Location: ../views/upload_shelter_documents.php');
    exit;
}

// Class for possible future use
class UploadShelterDocumentsController extends BaseController {
    // ...existing code...
}

?>