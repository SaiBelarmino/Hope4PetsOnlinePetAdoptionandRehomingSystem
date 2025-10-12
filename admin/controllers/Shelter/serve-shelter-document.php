<?php
namespace App\Controllers\Shelter;

// Serve shelter document file by id (admin only)
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid document id';
    exit;
}

require_once __DIR__ . '/../controllers/shelter-verification-requests-controller.php';
// Fetch document metadata
$row = ShelterVerificationRequestsController::getDocumentById($id);
if (!$row) {
    http_response_code(404);
    exit('Document not found');
}

$filePath = $row['file_path'] ?? '';
// Normalize backslashes
$filePathClean = str_replace('\\', '/', $filePath);

$baseDir = realpath(__DIR__ . '/../../');
$storageDir = realpath(__DIR__ . '/../../storage/documents');
$diskPath = false;
// For debugging: collect candidates tried
$debugMode = isset($_GET['debug']) && $_GET['debug'] == '1';
$debugInfo = [];

// Always try project-relative path first for DB paths like 'storage/documents/filename.ext'
$candidate = $baseDir . DIRECTORY_SEPARATOR . ltrim($filePathClean, '/');
$debugInfo[] = 'Trying project-relative candidate: ' . $candidate;
$diskPath = realpath($candidate) ?: false;
$debugInfo[] = 'project-relative realpath: ' . ($diskPath ?: 'false');

// If not found, try locating by basename inside storage/documents
if (!$diskPath && $storageDir && is_dir($storageDir)) {
    $baseName = basename($filePathClean);
    $matches = glob($storageDir . DIRECTORY_SEPARATOR . '*' . $baseName);
    if (!empty($matches)) {
        $debugInfo[] = 'Glob matches: ' . implode(', ', $matches);
        $diskPath = realpath($matches[0]);
        $debugInfo[] = 'glob resolved: ' . ($diskPath ?: 'false');
    } else {
        $debugInfo[] = 'Glob found no matches for basename ' . $baseName;
    }
}

// Ensure file exists and is within storage/documents or within project base
if (!$diskPath || !is_file($diskPath)) {
    if ($debugMode) {
        header('Content-Type: text/plain');
        echo "Debug report for document id={$id}\n";
        echo "DB file_path: {$filePath}\n";
        echo "BaseDir: {$baseDir}\n";
        echo "StorageDir: {$storageDir}\n";
        foreach ($debugInfo as $line) echo $line . "\n";
        echo "Resolved diskPath: " . ($diskPath ?: 'false') . "\n";
        http_response_code(404);
        exit;
    }
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Additional security: require file to be inside storage documents or inside project
$allowed = false;
if ($storageDir && strpos($diskPath, $storageDir) === 0) $allowed = true;
if (!$allowed && $baseDir && strpos($diskPath, $baseDir) === 0) $allowed = true;
if (!$allowed) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';
if (in_array($ext, ['jpg','jpeg'])) $mime = 'image/jpeg';
elseif ($ext === 'png') $mime = 'image/png';
elseif ($ext === 'gif') $mime = 'image/gif';
elseif ($ext === 'webp') $mime = 'image/webp';
elseif ($ext === 'pdf') $mime = 'application/pdf';

// Stream file with correct headers
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($diskPath));
header('Content-Disposition: inline; filename="' . basename($diskPath) . '"');
// Disable caching for admin-served files
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($diskPath);
exit;

?>