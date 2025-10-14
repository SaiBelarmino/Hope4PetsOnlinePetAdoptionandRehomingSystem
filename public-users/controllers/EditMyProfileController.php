<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// This controller handles the POST from edit_profile.php form
if (session_status() === PHP_SESSION_NONE) { session_start(); }
SessionManager::requireLogin();

$currentUser = SessionManager::getUser();
$userId = $currentUser['id'] ?? null;
if (!$userId) {
    http_response_code(403);
    exit('Unauthorized');
}

// Helper to set flash and redirect
function redirect_with_flash($message, $type = 'success') {
    SessionManager::setFlash($type, $message);
    header('Location: ../views/profile.php');
    exit;
}

$mysqli = BaseController::db();

// Process POST only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect fields (basic sanitization)
    $full_name = trim($_POST['full_name'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $show_email = isset($_POST['show_email']) ? 1 : 0;

    // Begin building update query
    $fields = ['full_name', 'birthday', 'gender', 'location', 'contact_number', 'show_email'];
    $params = [$full_name, $birthday ?: null, $gender ?: null, $location ?: null, $contact_number ?: null, $show_email, $userId];
    $stmt = $mysqli->prepare("UPDATE users SET full_name=?, birthday=?, gender=?, location=?, contact_number=?, show_email=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('ssssiii', $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6]);
        $stmt->execute();
        $stmt->close();
    }

    // Handle profile photo upload if provided
    if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            redirect_with_flash('Failed to upload profile photo.', 'danger');
        }
        // Validate mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            redirect_with_flash('Invalid image type. Allowed: jpg, png, webp.', 'danger');
        }
    // Ensure storage dir exists (store profile pictures under storage/uploads/images/profile-picture)
    $uploadsDir = __DIR__ . '/../../storage/uploads/images/profile-picture';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        // Generate unique filename
        $ext = $allowed[$mime];
        $filename = 'profile_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $uploadsDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            redirect_with_flash('Failed to save uploaded photo.', 'danger');
        }
    // Save relative path to DB (relative to project root)
    // Use 'storage/uploads/images/profile-picture/...' as requested
    $relativePath = 'storage/uploads/images/profile-picture/' . $filename;
        $stmt2 = $mysqli->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        if ($stmt2) {
            $stmt2->bind_param('si', $relativePath, $userId);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    // Refresh session user data
    $userRow = BaseController::fetchOne('SELECT id, full_name, email, is_verified, profile_photo FROM users WHERE id = ? LIMIT 1', 'i', [$userId]);
    if ($userRow) {
        SessionManager::login($userRow);
    }

    redirect_with_flash('Profile updated successfully.');
}

// If not POST, show simple 405
http_response_code(405);
exit('Method Not Allowed');

?>