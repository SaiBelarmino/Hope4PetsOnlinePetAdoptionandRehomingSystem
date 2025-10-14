<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

class EditMyProfileController extends BaseController {
    // This controller handles the POST from edit_profile.php form
    public function handlePost() {
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
            header('Location: ../views/MyProfile.php');
            exit;
        }

        $mysqli = $this->db();

        // Process POST only
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['full_name'])) {
                // Collect fields (basic sanitization)
                $full_name = trim($_POST['full_name'] ?? '');
                $birthday = trim($_POST['birthday'] ?? '');
                $gender = trim($_POST['gender'] ?? '');
                $shelter_unit = trim($_POST['shelter_unit'] ?? '');
                $postal_code = trim($_POST['postal_code'] ?? '');
                $purok_subdivision = trim($_POST['purok_subdivision'] ?? '');
                $barangay = trim($_POST['barangay'] ?? '');
                $city = trim($_POST['city'] ?? '');
                $province = trim($_POST['province'] ?? '');
                $contact_number = trim($_POST['contact_number'] ?? '');

                // Combine location parts
                $location_parts = array_filter([$shelter_unit, $postal_code, $purok_subdivision, $barangay, $city, $province]);
                $location = implode(', ', $location_parts);

                // Begin building update query
                $params = [$full_name, $birthday ?: null, $gender ?: null, $location ?: null, $contact_number ?: null, $userId];
                $stmt = $mysqli->prepare("UPDATE users SET full_name=?, birthday=?, gender=?, location=?, contact_number=? WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param('sssssi', $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
                    $stmt->execute();
                    $stmt->close();
                }
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
                // Ensure storage dir exists (store profile pictures under storage/uploads/profile_picture/{user_id})
                $uploadsDir = __DIR__ . '/../../storage/uploads/profile_picture/' . $userId;
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
                // Use 'storage/uploads/profile_picture/{user_id}/...' as requested
                $relativePath = 'storage/uploads/profile_picture/' . $userId . '/' . $filename;
                $stmt2 = $mysqli->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                if ($stmt2) {
                    $stmt2->bind_param('si', $relativePath, $userId);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }

            // Handle delete photo request
            if (isset($_POST['delete_photo'])) {
                $userId = $_POST['user_id'];
                $sql = "SELECT profile_photo FROM users WHERE id = ?";
                $user = self::fetchOne($sql, 'i', [$userId]);
                if ($user && $user['profile_photo'] && $user['profile_photo'] !== 'default-avatar.png') {
                    $filePath = __DIR__ . '/../../' . $user['profile_photo'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                $sql = "UPDATE users SET profile_photo = NULL WHERE id = ?";
                $stmt = self::db()->prepare($sql);
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->close();
                header('Location: ../views/MyProfile.php');
                exit;
            }

            // Refresh session user data
            $userRow = $this->fetchOne('SELECT id, full_name, email, is_verified, profile_photo FROM users WHERE id = ? LIMIT 1', 'i', [$userId]);
            if ($userRow) {
                SessionManager::login($userRow);
            }

            redirect_with_flash('Profile updated successfully.');
        }

        // If not POST, show simple 405
        http_response_code(405);
        exit('Method Not Allowed');
    }
}

// Instantiate and handle
$controller = new EditMyProfileController();
$controller->handlePost();
?>