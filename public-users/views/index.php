<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<link href="assets/css/index.css" rel="stylesheet">

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

<?php
$flash = \SessionManager::getFlash();
$flashSuccess = null;
$flashError = null;
if ($flash && is_array($flash)) {
    $t = $flash['type'] ?? null;
    $m = $flash['message'] ?? null;
    if ($t === 'success') { $flashSuccess = $m; }
    else { $flashError = $m; }
}
?>

<!-- Report Post Modal -->
<div class="modal fade" id="reportPostModal" tabindex="-1" aria-labelledby="reportPostModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportPostModalLabel">Report Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportPostForm" method="post" action="../controllers/ReportPostController.php">
                <div class="modal-body">
                    <input type="hidden" name="post_id" id="reportPostId" value="">
                    <div class="mb-3">
                        <label for="reportReason" class="form-label">Reason for reporting:</label>
                        <select class="form-select" name="reason" id="reportReason" required>
                            <option value="">Select a reason</option>
                            <option value="spam">Spam or misleading</option>
                            <option value="inappropriate">Inappropriate content</option>
                            <option value="harassment">Harassment or bullying</option>
                            <option value="false-info">False information</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reportDetails" class="form-label">Additional details (optional):</label>
                        <textarea class="form-control" name="details" id="reportDetails" rows="3" placeholder="Provide more information..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show report modal and set post id
document.addEventListener('DOMContentLoaded', function() {
    var reportModalEl = document.getElementById('reportPostModal');
    var reportModal = new bootstrap.Modal(reportModalEl);
    document.querySelectorAll('.report-post-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var postId = this.getAttribute('data-post-id');
            document.getElementById('reportPostId').value = postId;
            reportModal.show();
        });
    });
    // Remove backdrop when modal is hidden
    reportModalEl.addEventListener('hidden.bs.modal', function () {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(bd) { bd.parentNode.removeChild(bd); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });
});
</script>

<div class="container-fluid">
    <div class="row g-3 py-4">
        <!-- Left sidebar: shortcuts -->
        <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        <div class="col-12 col-lg-4"
            style="max-height:862px; overflow-y:auto; overflow-x:hidden; scrollbar-width:none; -ms-overflow-style:none;">
            <?php if ($flashSuccess): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof $ !== 'undefined' && typeof $.notify === 'function') {
                    $.notify(<?php echo json_encode((string)$flashSuccess, JSON_UNESCAPED_UNICODE); ?>, "success");
                } else if (typeof alert === 'function') {
                    alert(<?php echo json_encode((string)$flashSuccess, JSON_UNESCAPED_UNICODE); ?>);
                }
            });
            </script>
            <?php endif; ?>
            <?php if ($flashError): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof $ !== 'undefined' && typeof $.notify === 'function') {
                    $.notify(<?php echo json_encode((string)$flashError, JSON_UNESCAPED_UNICODE); ?>, "error");
                } else if (typeof alert === 'function') {
                    alert(<?php echo json_encode((string)$flashError, JSON_UNESCAPED_UNICODE); ?>);
                }
            });
            </script>
            <?php endif; ?>

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
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal" class="btn btn-primary mt-2"><i
                            class="ti ti-plus me-1"></i> Create Post</a>
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
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <a href="./UserProfile.php?user_id=<?php echo urlencode($post['user_id']); ?>">
                            <img src="<?php echo htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>"
                                class="rounded-circle me-2 object-fit-cover" width="36" height="36"
                                style="object-fit: cover; aspect-ratio: 1/1; min-width:36px; min-height:36px; max-width:36px; max-height:36px;"
                                alt="Profile picture" onerror="this.src='../../assets/images/profile/user-1.jpg'" />
                        </a>
                        <div class="flex-grow-1">
                            <a href="./UserProfile.php?user_id=<?php echo urlencode($post['user_id']); ?>"
                                class="fw-bold text-decoration-none text-dark"><?php echo htmlspecialchars($post['full_name']); ?></a>
                            <div class="text-muted small"><?php echo $timeAgo; ?></div>
                        </div>
                        <!-- 3-dots dropdown menu -->
                        <div class="dropdown ms-auto">
                            <button class="btn btn-link p-0 text-muted" type="button" id="postMenu<?php echo $post['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:1.5rem; text-decoration:none;">
                                <i class="ti ti-dots" style="text-decoration:none;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="postMenu<?php echo $post['id']; ?>">
                                <li><a class="dropdown-item report-post-link" href="#" data-post-id="<?php echo $post['id']; ?>">Report Post</a></li>
                                <!-- Report Post Modal -->
                                <div class="modal fade" id="reportPostModal" tabindex="-1" aria-labelledby="reportPostModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="reportPostModalLabel">Report Post</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form id="reportPostForm" method="post" action="../controllers/ReportPostController.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="post_id" id="reportPostId" value="">
                                                    <div class="mb-3">
                                                        <label for="reportReason" class="form-label">Reason for reporting:</label>
                                                        <select class="form-select" name="reason" id="reportReason" required>
                                                            <option value="">Select a reason</option>
                                                            <option value="spam">Spam or misleading</option>
                                                            <option value="inappropriate">Inappropriate content</option>
                                                            <option value="harassment">Harassment or bullying</option>
                                                            <option value="false-info">False information</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="reportDetails" class="form-label">Additional details (optional):</label>
                                                        <textarea class="form-control" name="details" id="reportDetails" rows="3" placeholder="Provide more information..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Submit Report</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                // Show report modal and set post id
                                document.addEventListener('DOMContentLoaded', function() {
                                    document.querySelectorAll('.report-post-link').forEach(function(link) {
                                        link.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            var postId = this.getAttribute('data-post-id');
                                            document.getElementById('reportPostId').value = postId;
                                            var modal = new bootstrap.Modal(document.getElementById('reportPostModal'));
                                            modal.show();
                                        });
                                    });
                                });
                                </script>
                                <?php if ($userId && $userId == $post['user_id']): ?>
                                <li><form method="post" action="../controllers/DeletePostController.php" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="dropdown-item text-danger">Delete Post</button>
                                </form></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <?php if (!empty($post['content'])): ?>
                    <?php
                        $fullContent = strip_tags($post['content']);
                        $maxLength = 100;
                        $shortText = strlen($fullContent) > $maxLength ? substr($fullContent, 0, $maxLength) . '...' : $fullContent;
                        $fullHtml = nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <div class="mb-2">
                        <div id="caption-<?php echo $post['id']; ?>"
                             class="post-caption-short"
                             data-short="<?php echo htmlspecialchars($shortText, ENT_QUOTES, 'UTF-8'); ?>"
                             data-full="<?php echo $fullHtml; ?>"
                             data-truncated="<?php echo strlen($fullContent) > $maxLength ? '1' : '0'; ?>">
                            <?php echo htmlspecialchars($shortText, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <!-- Replaced span with an accessible toggle button -->
                        <button type="button" class="btn btn-link p-0 mt-1 post-caption-toggle"
                                data-post="<?php echo $post['id']; ?>"
                                aria-controls="caption-<?php echo $post['id']; ?>"
                                aria-expanded="false"
                                aria-label="Toggle full caption"
                                onclick="toggleCaption(this)">See more</button>
                    </div>
                    <?php endif; ?>

                    <?php
                        $videos = IndexController::getPostVideos($post['id']);
                    ?>

                    <?php if (!empty($photos)): ?>
                    <?php if (count($photos) == 1): ?>
                    <div class="post-media-single mb-3">
                        <a href="./PostView.php?id=<?php echo urlencode($post['id']); ?>">
                            <img src="<?php echo htmlspecialchars(resolve_media_path($photos[0]['photo_path']), ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Post photo" onerror="this.style.display='none'"
                                style="cursor:pointer; max-width:100%; height:auto; object-fit:contain;" />
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="row row-cols-2 row-cols-md-2 g-2 mb-3"
                        onclick="window.location.href='./PostView.php?id=<?php echo urlencode($post['id']); ?>'"
                        style="cursor:pointer;">
                        <?php foreach (array_slice($photos, 0, 4) as $index => $photo): ?>
                        <div class="col">
                            <div style="position:relative; padding-top: 100%; overflow:hidden;">
                                <img src="<?php echo htmlspecialchars(resolve_media_path($photo['photo_path']), ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Post photo" onerror="this.style.display='none'"
                                    class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover rounded" />
                                <?php if ($index == 3 && count($photos) > 4): ?>
                                <div
                                    class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 rounded">
                                    <span class="text-white fs-4">+<?php echo count($photos) - 4; ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($videos)): ?>
                    <?php foreach ($videos as $v): ?>
                    <div class="video-container position-relative mb-3"
                        onclick="window.location.href='./PostView.php?id=<?php echo urlencode($post['id']); ?>'">
                        <video controls class="w-100" style="max-height:420px; aspect-ratio: 1; object-fit: cover;"
                            controlslist="nodownload">
                            <source
                                src="<?php echo htmlspecialchars(resolve_media_path($v['video_path']), ENT_QUOTES, 'UTF-8'); ?>" />
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between post-actions-sm mt-2">
                        <div class="action-group d-flex flex-wrap">
                            <a href="./PostView.php?id=<?php echo $post['id']; ?>"
                                class="btn btn-light border me-1 mb-1"><i class="ti ti-thumb-up"></i> <span
                                    class="d-none d-sm-inline">Like</span>
                                <?php if ($post['reaction_count'] > 0): ?><span
                                    class="badge bg-primary rounded-pill ms-1"><?php echo $post['reaction_count']; ?></span><?php endif; ?></a>
                            <!-- Replace Comment link with a toggle button that expands a collapsible comment form -->
                            <button type="button" class="btn btn-light border me-1 mb-1 comment-toggle"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#comment-box-<?php echo $post['id']; ?>"
                                    aria-expanded="false"
                                    aria-controls="comment-box-<?php echo $post['id']; ?>">
                                <i class="ti ti-message-circle"></i> <span class="d-none d-sm-inline">Comment</span>
                                <?php if ($post['comment_count'] > 0): ?><span
                                    class="badge bg-primary rounded-pill ms-1"><?php echo $post['comment_count']; ?></span><?php endif; ?>
                            </button>
                            <a href="./PostView.php?id=<?php echo urlencode($post['id']); ?>" class="btn btn-light border mb-1"><i
                                    class="ti ti-share"></i> <span class="d-none d-sm-inline">Share</span></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End single post card -->

            <!-- Collapsible inline comment box -->
            <?php if ($userId): ?>
            <div id="comment-box-<?php echo $post['id']; ?>" class="collapse comment-box mt-2">
                 <form method="post" action="../controllers/AddCommentController.php" class="d-flex align-items-start gap-2">
                    <img src="<?php echo htmlspecialchars($composerAvatar, ENT_QUOTES, 'UTF-8'); ?>"
                         class="rounded-circle object-fit-cover" width="32" height="32" alt="Your avatar"
                         style="object-fit:cover; aspect-ratio:1/1;">
                    <div class="flex-grow-1">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="position-relative">
                            <input type="text" name="content" class="form-control pe-5" placeholder="Write a comment..." required maxlength="1000">
                            <button type="submit" class="btn btn-primary position-absolute top-50 end-0 translate-middle-y me-2 p-0 d-inline-flex align-items-center justify-content-center" style="width:32px; height:32px; border-radius:50%;" aria-label="Send comment">
                                <i class="ti ti-send"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <br>
            <?php else: ?>
            <div id="comment-box-<?php echo $post['id']; ?>" class="collapse comment-box mt-2">
                <div class="alert alert-info mb-0">
                    Please <a href="../login.php" class="alert-link">log in</a> to comment.
                </div>
            </div>
            <?php endif; ?>

            <?php endforeach; ?>
            <?php endif; ?>
            <!-- Create Post Modal -->
            <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createPostModalLabel">Create Post</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createPostForm" method="post" action="../controllers/CreatePostController.php"
                                enctype="multipart/form-data">
                                <input type="hidden" name="action" value="create" />
                                <div class="mb-3">
                                    <label for="content" class="form-label">What's on your mind?</label>
                                    <textarea id="content" name="content" class="form-control" rows="4"
                                        placeholder="Put something here, or leave empty and attach images/videos."></textarea>
                                </div>
                                <div class="mb-3">
                                    <div id="drop-zone"
                                        class="border border-dashed border-secondary rounded p-4 text-center"
                                        style="min-height: 200px; cursor: pointer; position:relative;">
                                        <p>Drag and drop images or videos here or click to select</p>

                                        <div style="display:inline-block; position:relative;">
                                            <button type="button" id="select-media-btn" class="btn btn-light border mt-2" style="position:relative; overflow:hidden;">
                                                Choose files
                                                <input type="file" id="media" name="media[]" accept="image/*,video/*" multiple
                                                    style="position:absolute; left:0; top:0; width:100%; height:100%; opacity:0; cursor:pointer;" />
                                            </button>
                                        </div>

                                        <!-- Persistent preview area -->
                                        <div id="media-previews" class="media-previews mt-3 d-flex flex-wrap gap-2" aria-live="polite">
                                            <div class="text-muted small">No files selected</div>
                                        </div>

                                    </div>
                                </div>
                                <small class="text-muted">You can add multiple images. Max recommended: 8 images. One
                                    video per post.</small>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Post</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php include __DIR__ . '/../include/footer.php'; ?>
            <script src="assets/js/index.js?v=3"></script>