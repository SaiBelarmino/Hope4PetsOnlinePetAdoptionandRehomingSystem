<?php
require_once __DIR__ . '/../../../controllers/BaseController.php';

class ProfileSettingsController extends BaseController
{
    public static function get(int $adminId): ?array
    {
        // Updated to fetch all necessary profile fields
        return self::fetchOne("SELECT id, full_name, username, email, phone_number, profile_picture FROM admins WHERE id = ?", 'i', [$adminId]);
    }

    public static function updateProfile(int $adminId, array $data, ?array $file): array
    {
        $errors = [];
        $updateData = [];
        $types = '';
        $params = [];

        // Handle file upload
        if (isset($file['profile_picture']) && $file['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../../public/uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '-' . basename($file['profile_picture']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($file['profile_picture']['tmp_name'], $targetPath)) {
                $data['profile_picture'] = 'public/uploads/profile_pictures/' . $fileName;
            } else {
                $errors[] = "Failed to upload profile picture.";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Prepare data for SQL update
        $allowedFields = ['full_name', 'username', 'email', 'phone_number', 'profile_picture'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[] = "$field = ?";
                $types .= 's'; // Assuming all fields are strings for simplicity here
                $params[] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return ['success' => true, 'message' => 'No changes detected.'];
        }

        $sql = "UPDATE admins SET " . implode(', ', $updateData) . " WHERE id = ?";
        $types .= 'i';
        $params[] = $adminId;

        if (self::execute($sql, $types, $params)) {
            return ['success' => true, 'message' => 'Profile updated successfully.'];
        } else {
            return ['success' => false, 'errors' => ['Failed to update profile.']];
        }
    }

    public static function changePassword(int $adminId, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        $adminData = self::fetchOne("SELECT password_hash FROM admins WHERE id = ?", 'i', [$adminId]);

        if (!$adminData || !password_verify($currentPassword, $adminData['password_hash'])) {
            return ['success' => false, 'errors' => ['Invalid current password.']];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'errors' => ['New passwords do not match.']];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'errors' => ['Password must be at least 8 characters long.']];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE admins SET password_hash = ? WHERE id = ?";

        if (self::execute($sql, 'si', [$hashedPassword, $adminId])) {
            return ['success' => true, 'message' => 'Password changed successfully.'];
        } else {
            return ['success' => false, 'errors' => ['Failed to change password.']];
        }
    }
}
?>