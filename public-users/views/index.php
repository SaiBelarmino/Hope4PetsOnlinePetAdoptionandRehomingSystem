<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../controllers/index-controller.php';

$pageTitle = 'Community Feed';
// Composer display name (ensure we use the same session key used elsewhere)
$displayName = !empty($_SESSION['user']['full_name']) ? $_SESSION['user']['full_name'] : 'Share something';
$userId = $_SESSION['user']['id'] ?? null;
// Composer avatar resolved from stored profile_photo
if (!function_exists('resolve_profile_photo')) { include __DIR__ . '/../include/profile_helpers.php'; }
$composerAvatar = resolve_profile_photo($_SESSION['user']['profile_photo'] ?? null);

// Get recent posts from database
$posts = IndexController::getRecentPosts(20);
?>

<?php
// Helper to resolve media paths to URLs similar to resolve_profile_photo
function resolve_media_path(?string $path): string {
    if (empty($path)) return '../../assets/images/placeholder.png';
    $p = trim($path);
    if (preg_match('#^https?://#i', $p)) return $p;
    $normalized = str_replace('\\', '/', $p);
    $pos = stripos($normalized, 'storage/');
    if ($pos !== false) {
        $sub = substr($normalized, $pos);
        return '../../' . ltrim($sub, '/');
    }
    $normalized = preg_replace('#^(\.{1,2}/)+#', '', $normalized);
    $normalized = ltrim($normalized, '/');
    if (stripos($normalized, 'storage/') === 0) return '../../' . $normalized;
    if (stripos($normalized, 'uploads/') === 0) return '../../storage/' . ltrim($normalized, '/');
    return '../../' . $normalized;
}
?>

<style>
/* Post media styling: single image fills width with controlled height and object-fit; grid thumbs are uniform */
.post-media-single img {
    width: 100%;
    max-height: 420px;
    height: 420px;
    object-fit: cover;
    display: block;
    border-radius: 8px;
}

.post-media-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.post-media-grid img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
}

@media (min-width: 992px) {
    .post-media-grid img {
        height: 200px;
    }
}
</style>

<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Left sidebar: shortcuts -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
        <!-- Center: composer and feed -->
        <div class="col-12 col-lg-6" style="max-height:862px; overflow-y:auto; overflow-x:hidden; scrollbar-width:none; -ms-overflow-style:none;">
            <!-- Composer -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <img src="<?php echo htmlspecialchars($composerAvatar, ENT_QUOTES, 'UTF-8'); ?>"
                            class="rounded-circle me-3 object-fit-cover" width="44" height="44"
                            alt="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>'s avatar"
                            style="object-fit:cover;" />
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal"
                            class="form-control text-start text-muted text-decoration-none"
                            style="text-decoration:none;">
                            <i class="ti ti-edit me-2"
                                aria-hidden="true"></i><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </div>
                    <div class="d-flex gap-2 mt-3 composer-actions btn-stack-sm">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal"
                            class="btn btn-light border"><i class="ti ti-photo me-1 text-success"></i> Photo</a>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal"
                            class="btn btn-light border"><i class="ti ti-video me-1 text-danger"></i> Video</a>
                    </div>
                </div>
            </div>

            <!-- Feed items from database -->
            <?php if (empty($posts)): ?>
            <div class="card mb-3">
                <div class="card-body text-center py-5">
                    <i class="ti ti-mood-empty" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">No posts yet. Be the first to share something!</p>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal" class="btn btn-primary mt-2">
                        <i class="ti ti-plus me-1"></i> Create Post
                    </a>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($posts as $post): 
                    // Get post photos
                    $photos = IndexController::getPostPhotos($post['id']);
                    
                    // Format date
                    $postDate = new DateTime($post['created_at']);
                    $now = new DateTime();
                    $interval = $now->diff($postDate);
                    
                    if ($interval->d == 0) {
                        if ($interval->h == 0) {
                            $timeAgo = $interval->i . ' min ago';
                        } else {
                            $timeAgo = $interval->h . ' hr' . ($interval->h > 1 ? 's' : '') . ' ago';
                        }
                    } elseif ($interval->d == 1) {
                        $timeAgo = 'Yesterday';
                    } elseif ($interval->d < 7) {
                        $timeAgo = $interval->d . ' days ago';
                    } else {
                        $timeAgo = $postDate->format('M d, Y');
                    }
                    
                    // Get profile photo or default using helper
                    $profilePhoto = resolve_profile_photo($post['profile_photo'] ?? null);
                ?>

            <!-- Single post card -->
            <div class="card mb-3">
                <!-- Make only this card body scrollable -->
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <a href="./profile.php?user_id=<?php echo urlencode($post['user_id']); ?>">
                            <img src="<?php echo htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>"
                                class="rounded-circle me-2 object-fit-cover" width="36" height="36"
                                style="object-fit: cover; aspect-ratio: 1/1; min-width:36px; min-height:36px; max-width:36px; max-height:36px;"
                                alt="Profile picture" onerror="this.src='../../assets/images/profile/user-1.jpg'" />
                        </a>
                        <div>
                            <a href="./profile.php?user_id=<?php echo urlencode($post['user_id']); ?>"
                                class="fw-bold text-decoration-none text-dark">
                                <?php echo htmlspecialchars($post['full_name']); ?>
                            </a>
                            <div class="text-muted small"><?php echo $timeAgo; ?></div>
                        </div>
                    </div>

                    <?php if (!empty($post['content'])): ?>
                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <?php endif; ?>

                    <?php
                        $videos = IndexController::getPostVideos($post['id']);
                    ?>

                    <?php if (!empty($photos)): ?>
                    <?php if (count($photos) == 1): ?>
                    <div class="post-media-single mb-3">
                        <img src="<?php echo htmlspecialchars(resolve_media_path($photos[0]['photo_path']), ENT_QUOTES, 'UTF-8'); ?>"
                            alt="Post photo" onerror="this.style.display='none'" />
                    </div>
                    <?php else: ?>
                    <div class="post-media-grid mb-3">
                        <?php foreach (array_slice($photos, 0, 4) as $index => $photo): ?>
                        <div style="position:relative;">
                            <a href="<?php echo htmlspecialchars(resolve_media_path($photo['photo_path']), ENT_QUOTES, 'UTF-8'); ?>"
                                target="_blank" rel="noopener">
                                <img src="<?php echo htmlspecialchars(resolve_media_path($photo['photo_path']), ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Post photo" onerror="this.style.display='none'" />
                            </a>
                            <?php if ($index == 3 && count($photos) > 4): ?>
                            <div
                                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 rounded">
                                <span class="text-white fs-4">+<?php echo count($photos) - 4; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($videos)): ?>
                    <?php foreach ($videos as $v): ?>
                    <div class="mb-3">
                        <video controls class="w-100" style="max-height:420px;">
                            <source
                                src="<?php echo htmlspecialchars(resolve_media_path($v['video_path']), ENT_QUOTES, 'UTF-8'); ?>" />
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between post-actions-sm mt-2">
                        <div class="action-group d-flex flex-wrap">
                            <a href="./post_view.php?id=<?php echo $post['id']; ?>"
                                class="btn btn-light border me-1 mb-1">
                                <i class="ti ti-thumb-up"></i>
                                <span class="d-none d-sm-inline">Like</span>
                                <?php if ($post['reaction_count'] > 0): ?>
                                <span
                                    class="badge bg-primary rounded-pill ms-1"><?php echo $post['reaction_count']; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="./post_view.php?id=<?php echo $post['id']; ?>"
                                class="btn btn-light border me-1 mb-1">
                                <i class="ti ti-message-circle"></i>
                                <span class="d-none d-sm-inline">Comment</span>
                                <?php if ($post['comment_count'] > 0): ?>
                                <span
                                    class="badge bg-primary rounded-pill ms-1"><?php echo $post['comment_count']; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="./post_view.php?id=<?php echo $post['id']; ?>" class="btn btn-light border mb-1">
                                <i class="ti ti-share"></i>
                                <span class="d-none d-sm-inline">Share</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End single post card -->

            <?php endforeach; ?>
            <?php endif; ?>
            <?php include __DIR__ . '/../include/footer.php'; ?>