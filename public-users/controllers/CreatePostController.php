<?php
/**
 * Create Post Controller
 * 
 * Handles post creation with optional pet linking and photo uploads.
 * Posts are linked to the logged-in user's account.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Define class BEFORE usage to avoid fatal "class not found".
class PublicCreatePostController extends BaseController {
    /**
     * Create a new post
     * 
     * @param int $userId User ID creating the post
     * @param array $data Post data (content, pet_id, photos)
     * @return array Result with success status and message
     */
    public static function create(int $userId, array $data): array {
        $mysqli = self::db();
    $projectRoot = realpath(__DIR__ . '/../../');
    $storageRoot = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'posts';
        
        // Insert post
        $stmt = $mysqli->prepare(
            "INSERT INTO posts (user_id, pet_id, content, created_at) 
             VALUES (?, ?, ?, NOW())"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        
        $petId = $data['pet_id'] ?? null;
        $content = $data['content'];
        
        $stmt->bind_param('iis', $userId, $petId, $content);
        $success = $stmt->execute();
        
        if (!$success) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create post.'];
        }
        
        $postId = $mysqli->insert_id;
        $stmt->close();
        
    // Insert photos if any
        if (!empty($data['photos'])) {
            $errorsPhotos = [];
            $inserted = 0;
            $photoStmt = $mysqli->prepare("INSERT INTO post_photos (post_id, photo_path) VALUES (?, ?)");
            $petPhotoStmt = !empty($petId) ? $mysqli->prepare("INSERT INTO pet_photos (pet_id, photo_path, is_primary) VALUES (?, ?, 0)") : null;
            foreach ($data['photos'] as $relPath) {
                $relPathClean = self::sanitizeRelative($relPath); // storage/uploads/posts/filename.ext
                // No further path manipulation - sanitizeRelative already cleaned it
                $absPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relPathClean);
                if ($photoStmt && $photoStmt->bind_param('is', $postId, $relPathClean)) {
                    if ($photoStmt->execute()) { $inserted++; } else { $errorsPhotos[] = 'post_photos:'.$mysqli->error; }
                }
                if ($petPhotoStmt && $petPhotoStmt->bind_param('is', $petId, $absPath)) {
                    if (!$petPhotoStmt->execute()) { $errorsPhotos[] = 'pet_photos:'.$mysqli->error; }
                }
            }
            if ($photoStmt) $photoStmt->close();
            if ($petPhotoStmt) $petPhotoStmt->close();
            if ($inserted === 0) {
                $errorsPhotos[] = 'No rows inserted into post_photos.';
            }
            if ($errorsPhotos) {
                self::logUploadErrors($errorsPhotos);
                SessionManager::setFlash('error', 'Photo insert issues: '.implode('; ', $errorsPhotos));
            }
        }
        // Insert video if provided
        if (!empty($data['video'])) {
            $videoPath = self::sanitizeRelative($data['video']);
            $videoStmt = $mysqli->prepare("INSERT INTO post_videos (post_id, video_path) VALUES (?, ?)");
            if ($videoStmt && $videoStmt->bind_param('is', $postId, $videoPath)) {
                if (!$videoStmt->execute()) {
                    self::logUploadErrors(['post_videos:'.$mysqli->error]);
                    SessionManager::setFlash('error', 'Failed to save video metadata.');
                }
            }
            if ($videoStmt) $videoStmt->close();
        }
        
        return ['success' => true, 'message' => 'Post created successfully!', 'post_id' => $postId];
    }

    private static function logUploadErrors(array $errs): void {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
        $line = date('Y-m-d H:i:s') . ' POST_PHOTO_ERRORS ' . implode(' | ', $errs) . "\n";
        @file_put_contents($logDir . '/app.log', $line, FILE_APPEND);
    }

    private static function sanitizeRelative(string $path): string {
        $p = str_replace(['\\\\','\\'], '/', trim($path));
        $p = preg_replace('#/+#','/',$p);
        $p = ltrim($p,'/');
        return $p;
    }
    
    /**
     * Get posts by user ID
     */
    public static function getPostsByUserId(int $userId, int $limit = 20, int $offset = 0): array {
        return self::fetchAll(
            "SELECT p.*, u.full_name, u.profile_photo,
                    (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as reaction_count,
                    (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            'iii',
            [$userId, $limit, $offset]
        );
    }

    /**
     * Update an existing post
     */
    public static function update(int $postId, int $userId, array $data): array {
        $mysqli = self::db();

        // Verify ownership
        $post = self::fetchOne("SELECT user_id FROM posts WHERE id = ?", 'i', [$postId]);
        if (!$post || $post['user_id'] != $userId) {
            return ['success' => false, 'message' => 'You do not have permission to edit this post.'];
        }

        // Update post
        $stmt = $mysqli->prepare("UPDATE posts SET content = ?, pet_id = ? WHERE id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }

        $petId = $data['pet_id'] ?? null;
        $content = $data['content'];

        $stmt->bind_param('sii', $content, $petId, $postId);
        $success = $stmt->execute();
        $stmt->close();

        if (!$success) {
            return ['success' => false, 'message' => 'Failed to update post.'];
        }

        // Handle new photos if any
        if (!empty($data['photos'])) {
            $projectRoot = realpath(__DIR__ . '/../../');
            $errorsPhotos = [];
            $photoStmt = $mysqli->prepare("INSERT INTO post_photos (post_id, photo_path) VALUES (?, ?)");
            foreach ($data['photos'] as $relPath) {
                $relPathClean = self::sanitizeRelative($relPath);
                if ($photoStmt && $photoStmt->bind_param('is', $postId, $relPathClean)) {
                    if (!$photoStmt->execute()) {
                        $errorsPhotos[] = 'post_photos:' . $mysqli->error;
                    }
                }
            }
            if ($photoStmt) $photoStmt->close();
            if ($errorsPhotos) {
                self::logUploadErrors($errorsPhotos);
            }
        }
        // Handle new video if any
        if (!empty($data['video'])) {
            $videoPath = self::sanitizeRelative($data['video']);
            $videoStmt = $mysqli->prepare("INSERT INTO post_videos (post_id, video_path) VALUES (?, ?)");
            if ($videoStmt && $videoStmt->bind_param('is', $postId, $videoPath)) {
                if (!$videoStmt->execute()) {
                    self::logUploadErrors(['post_videos:'.$mysqli->error]);
                }
            }
            if ($videoStmt) $videoStmt->close();
        }

        return ['success' => true, 'message' => 'Post updated successfully!'];
    }

    /**
     * Delete a post
     */
    public static function delete(int $postId, int $userId): array {
        $mysqli = self::db();

        // Verify ownership
        $post = self::fetchOne("SELECT user_id FROM posts WHERE id = ?", 'i', [$postId]);
        if (!$post || $post['user_id'] != $userId) {
            return ['success' => false, 'message' => 'You do not have permission to delete this post.'];
        }

        // Delete related photos from database and filesystem
        $photos = self::fetchAll("SELECT photo_path FROM post_photos WHERE post_id = ?", 'i', [$postId]);
        $projectRoot = realpath(__DIR__ . '/../../');
        foreach ($photos as $photo) {
            $filePath = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $photo['photo_path']);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Delete from database (cascade should handle reactions and comments if set up)
        $mysqli->query("DELETE FROM post_photos WHERE post_id = $postId");
        $mysqli->query("DELETE FROM post_reactions WHERE post_id = $postId");
        $mysqli->query("DELETE FROM post_comments WHERE post_id = $postId");

        $stmt = $mysqli->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param('i', $postId);
        $success = $stmt->execute();
        $stmt->close();

        return ['success' => $success, 'message' => $success ? 'Post deleted successfully!' : 'Failed to delete post.'];
    }

    /**
     * Get a single post for editing
     */
    public static function getPostForEdit(int $postId, int $userId): ?array {
        $post = self::fetchOne(
            "SELECT p.*, 
                    (SELECT GROUP_CONCAT(photo_path) FROM post_photos WHERE post_id = p.id) as photos
             FROM posts p 
             WHERE p.id = ? AND p.user_id = ?",
            'ii',
            [$postId, $userId]
        );
        return $post;
    }
}

// Require login
SessionManager::requireLogin();

// Handle post creation form submission AFTER class is defined
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = SessionManager::getUserId();
    $action = $_POST['action'] ?? 'create';

    // Handle DELETE action
    if ($action === 'delete') {
        $postId = (int)($_POST['post_id'] ?? 0);
        if ($postId > 0) {
            $result = PublicCreatePostController::delete($postId, $userId);
            if ($result['success']) {
                SessionManager::setFlash('success', $result['message']);
                header('Location: ../views/index.php');
                exit;
            }
            SessionManager::setFlash('error', $result['message']);
            header('Location: ../views/post_view.php?id=' . $postId);
            exit;
        }
        SessionManager::setFlash('error', 'Invalid post ID.');
        header('Location: ../views/index.php');
        exit;
    }

    // Handle EDIT/UPDATE action
    if ($action === 'update') {
        $postId = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $petId = !empty($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;

        $errors = [];

        // Allow empty content if user uploads photos/video or if the post already has media.
        $hasNewPhotos = !empty($_FILES['photos']['name'][0]);
        $hasNewVideo = (!empty($_FILES['video']) && !empty($_FILES['video']['tmp_name']));
        $existingPost = self::getPostForEdit($postId, $userId);
        $hasExistingMedia = !empty($existingPost['photos']);

        if ($content === '' && !$hasNewPhotos && !$hasNewVideo && !$hasExistingMedia) {
            $errors[] = 'Please add text or attach images/videos.';
        }

        // Photo uploads for update
        $uploadedPhotos = [];
        $uploadedVideo = null;
        if (!empty($_FILES['photos']['name'][0])) {
            $uploadDir = __DIR__ . '/../../storage/uploads/posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
            $maxFileSize = 5 * 1024 * 1024;
            foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
                if (!$tmp) continue;
                $name = $_FILES['photos']['name'][$i];
                $size = $_FILES['photos']['size'][$i];
                $type = $_FILES['photos']['type'][$i];
                $err  = $_FILES['photos']['error'][$i];
                if ($err !== UPLOAD_ERR_OK) { $errors[] = "Error uploading $name"; continue; }
                if (!in_array($type, $allowedTypes, true)) { $errors[] = "$name invalid file type"; continue; }
                if ($size > $maxFileSize) { $errors[] = "$name too large (max 5MB)"; continue; }
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new = uniqid('post_') . '_' . time() . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $new)) {
                    $uploadedPhotos[] = 'storage/uploads/posts/' . $new;
                } else {
                    $errors[] = "Failed to upload $name";
                }
            }
        }
        // Video upload for update (single)
        if (!empty($_FILES['video']) && !empty($_FILES['video']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../../storage/uploads/posts/videos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowedVideoTypes = ['video/mp4','video/webm','video/ogg','video/quicktime'];
            $maxVideoSize = 50 * 1024 * 1024; // 50MB
            $vtmp = $_FILES['video']['tmp_name'];
            $vname = $_FILES['video']['name'];
            $vsize = $_FILES['video']['size'];
            $vtype = $_FILES['video']['type'];
            $verr  = $_FILES['video']['error'];
            if ($verr !== UPLOAD_ERR_OK) { $errors[] = "Error uploading video $vname"; }
            elseif (!in_array($vtype, $allowedVideoTypes, true)) { $errors[] = "$vname invalid video type"; }
            elseif ($vsize > $maxVideoSize) { $errors[] = "$vname too large (max 50MB)"; }
            else {
                $vext = pathinfo($vname, PATHINFO_EXTENSION);
                $vnew = uniqid('post_vid_') . '_' . time() . '.' . $vext;
                if (move_uploaded_file($vtmp, $uploadDir . $vnew)) {
                    $uploadedVideo = 'storage/uploads/posts/videos/' . $vnew;
                } else {
                    $errors[] = "Failed to upload video $vname";
                }
            }
        }

        if (!$errors) {
            $result = PublicCreatePostController::update($postId, $userId, [
                'content' => $content,
                'pet_id' => $petId,
                'photos' => $uploadedPhotos,
                'video' => $uploadedVideo
            ]);
            if ($result['success']) {
                SessionManager::setFlash('success', $result['message']);
                header('Location: ../views/post_view.php?id=' . $postId);
                exit;
            }
            SessionManager::setFlash('error', $result['message']);
            header('Location: ../views/create_post.php?edit=' . $postId);
            exit;
        }
        SessionManager::setFlash('error', implode('<br>', $errors));
        header('Location: ../views/create_post.php?edit=' . $postId);
        exit;
    }

    // Handle CREATE action (default)
    $content = trim($_POST['content'] ?? '');
    $petId = !empty($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;

    $errors = [];

    // Allow empty content if user uploads photos or video
    $hasPhotosInRequest = !empty($_FILES['photos']['name'][0]);
    $hasVideoInRequest = (!empty($_FILES['video']) && !empty($_FILES['video']['tmp_name']));
    if ($content === '' && !$hasPhotosInRequest && !$hasVideoInRequest) {
        $errors[] = 'Please add text or attach images/videos.';
    }

    // Photo uploads
    $uploadedPhotos = [];
    $uploadedVideo = null;
    if (!empty($_FILES['photos']['name'][0])) {
        $uploadDir = __DIR__ . '/../../storage/uploads/posts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
        $maxFileSize = 5 * 1024 * 1024;
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
            if (!$tmp) continue;
            $name = $_FILES['photos']['name'][$i];
            $size = $_FILES['photos']['size'][$i];
            $type = $_FILES['photos']['type'][$i];
            $err  = $_FILES['photos']['error'][$i];
            if ($err !== UPLOAD_ERR_OK) { $errors[] = "Error uploading $name"; continue; }
            if (!in_array($type, $allowedTypes, true)) { $errors[] = "$name invalid file type"; continue; }
            if ($size > $maxFileSize) { $errors[] = "$name too large (max 5MB)"; continue; }
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $new = uniqid('post_') . '_' . time() . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $new)) {
                // Store relative path (no leading slash). View will prepend APP_BASE_PATH.
                $uploadedPhotos[] = 'storage/uploads/posts/' . $new;
            } else {
                $errors[] = "Failed to upload $name";
            }
        }
    }
    // Video upload for create (single)
    if (!empty($_FILES['video']) && !empty($_FILES['video']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../../storage/uploads/posts/videos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $allowedVideoTypes = ['video/mp4','video/webm','video/ogg','video/quicktime'];
        $maxVideoSize = 50 * 1024 * 1024; // 50MB
        $vtmp = $_FILES['video']['tmp_name'];
        $vname = $_FILES['video']['name'];
        $vsize = $_FILES['video']['size'];
        $vtype = $_FILES['video']['type'];
        $verr  = $_FILES['video']['error'];
        if ($verr !== UPLOAD_ERR_OK) { $errors[] = "Error uploading video $vname"; }
        elseif (!in_array($vtype, $allowedVideoTypes, true)) { $errors[] = "$vname invalid video type"; }
        elseif ($vsize > $maxVideoSize) { $errors[] = "$vname too large (max 50MB)"; }
        else {
            $vext = pathinfo($vname, PATHINFO_EXTENSION);
            $vnew = uniqid('post_vid_') . '_' . time() . '.' . $vext;
            if (move_uploaded_file($vtmp, $uploadDir . $vnew)) {
                $uploadedVideo = 'storage/uploads/posts/videos/' . $vnew;
            } else {
                $errors[] = "Failed to upload video $vname";
            }
        }
    }

    if (!$errors) {
        $result = PublicCreatePostController::create($userId, [
            'content' => $content,
            'pet_id' => $petId,
            'photos' => $uploadedPhotos,
            'video' => $uploadedVideo
        ]);
        if (!empty($result['success'])) {
            // Redirect back to the create post view and show the newly created post in a modal
            $pid = (int)($result['post_id'] ?? 0);
            header('Location: ../views/create_post.php?created=' . $pid);
            exit;
        }
        SessionManager::setFlash('error', $result['message'] ?? 'Failed to create post.');
        header('Location: ../views/create_post.php');
        exit;
    }
    SessionManager::setFlash('error', implode('<br>', $errors));
    header('Location: ../views/create_post.php');
    exit;
}
?>