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
                            style="object-fit:cover;"
                            onerror="this.onerror=null;this.src='../../assets/images/profile/user-1.jpg';" />
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
                    // Get heart count and if user hearted
                    $heartCount = 0;
                    $userHearted = false;
                    try {
                        $conn = $GLOBALS['conn'] ?? null;
                        if (!$conn) {
                            require_once __DIR__ . '/../config/db-connection/db_connection.php';
                            $conn = $GLOBALS['conn'] ?? null;
                        }
                        if ($conn) {
                            $stmt = $conn->prepare('SELECT COUNT(*) FROM post_reactions WHERE post_id=?');
                            $stmt->bind_param('i', $post['id']);
                            $stmt->execute();
                            $stmt->bind_result($heartCount);
                            $stmt->fetch();
                            $stmt->close();
                            if ($userId) {
                                $stmt = $conn->prepare('SELECT 1 FROM post_reactions WHERE post_id=? AND user_id=? LIMIT 1');
                                $stmt->bind_param('ii', $post['id'], $userId);
                                $stmt->execute();
                                $stmt->store_result();
                                $userHearted = $stmt->num_rows > 0;
                                $stmt->close();
                            }
                        }
                    } catch (Throwable $e) {}
                ?>

            <!-- Single post card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <a href="./UserProfile.php?user_id=<?php echo urlencode($post['user_id']); ?>">
                            <img src="<?php echo htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>"
                                class="rounded-circle me-2 object-fit-cover" width="36" height="36"
                                style="object-fit: cover; aspect-ratio: 1/1; min-width:36px; min-height:36px; max-width:36px; max-height:36px;"
                                alt="Profile picture"
                                onerror="this.onerror=null;this.src='../../assets/images/profile/user-1.jpg';" />
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
                            <button type="button" class="heart-btn btn btn-light border me-1 mb-1<?php if ($userHearted) echo ' hearted'; ?>"
                                data-post-id="<?php echo $post['id']; ?>" data-user-heart="<?php echo $userHearted ? '1' : '0'; ?>" <?php if (!$userId) echo 'disabled title="Log in to heart"'; ?>>
                                <span class="heart-icon align-middle"></span>
                                <span class="d-none d-sm-inline">Heart</span>
                                <span class="heart-count ms-1"><?php echo (int)$heartCount; ?></span>
                            </button>
                            <link href="assets/css/HeartReaction.css?v=1" rel="stylesheet">
                            <script src="assets/js/HeartReaction.js?v=1"></script>
                            <!-- Comment button triggers modal -->
                            <button type="button" class="btn btn-light border me-1 mb-1 comment-modal-btn"
                                    data-post-id="<?php echo $post['id']; ?>"
                                    data-post-content="<?php echo isset($fullHtml) ? htmlspecialchars($fullHtml, ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    data-post-user="<?php echo htmlspecialchars($post['full_name']); ?>"
                                    data-post-avatar="<?php echo htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-post-date="<?php echo htmlspecialchars($timeAgo); ?>"
                                    data-comment-count="<?php echo $post['comment_count']; ?>">
                                <i class="ti ti-message-circle"></i> <span class="d-none d-sm-inline">Comment</span>
                                <?php if ($post['comment_count'] > 0): ?><span
                                    class="badge bg-primary rounded-pill ms-1"><?php echo $post['comment_count']; ?></span><?php endif; ?>
                            </button>
                            <button type="button" class="btn btn-light border mb-1 share-btn"
                                data-post-id="<?php echo $post['id']; ?>"
                                data-post-url="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?') . '?post_id=' . $post['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="ti ti-share"></i> <span class="d-none d-sm-inline">Share</span>
                            </button>
                        <!-- Share Post Modal -->
                        <div class="modal fade" id="sharePostModal" tabindex="-1" aria-labelledby="sharePostModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="sharePostModalLabel">Share Post</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p>Share this post on:</p>
                                        <div class="d-flex justify-content-center gap-3 mb-3">
                                            <a id="shareFacebook" class="btn btn-primary" target="_blank" rel="noopener">
                                                <i class="ti ti-brand-facebook"></i> Facebook
                                            </a>
                                            <a id="shareInstagram" class="btn btn-danger" target="_blank" rel="noopener">
                                                <i class="ti ti-brand-instagram"></i> Instagram
                                            </a>
                                            <a id="shareTiktok" class="btn btn-dark" target="_blank" rel="noopener">
                                                <i class="ti ti-brand-tiktok"></i> TikTok
                                            </a>
                                        </div>
                                        <input type="text" id="sharePostUrl" class="form-control text-center" readonly value="" style="font-size:0.95em;">
                                        <button class="btn btn-outline-secondary mt-2" id="copyShareUrlBtn">Copy Link</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                        // Share modal logic
                        document.addEventListener('DOMContentLoaded', function() {
                            var shareModalEl = document.getElementById('sharePostModal');
                            var shareModal = new bootstrap.Modal(shareModalEl);
                            var shareUrlInput = document.getElementById('sharePostUrl');
                            var copyBtn = document.getElementById('copyShareUrlBtn');
                            var fbBtn = document.getElementById('shareFacebook');
                            var igBtn = document.getElementById('shareInstagram');
                            var tiktokBtn = document.getElementById('shareTiktok');

                            document.querySelectorAll('.share-btn').forEach(function(btn) {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    var postUrl = this.getAttribute('data-post-url');
                                    shareUrlInput.value = postUrl;
                                    // Facebook share
                                    fbBtn.href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(postUrl);
                                    // Instagram does not support direct web sharing, so we copy the link and show a message
                                    igBtn.onclick = function(ev) {
                                        ev.preventDefault();
                                        navigator.clipboard.writeText(postUrl).then(function() {
                                            alert('Link copied! Open Instagram and paste it in your story or bio.');
                                        });
                                    };
                                    // TikTok: no direct web share, but we can copy the link and prompt
                                    tiktokBtn.onclick = function(ev) {
                                        ev.preventDefault();
                                        navigator.clipboard.writeText(postUrl).then(function() {
                                            alert('Link copied! Open TikTok and share it in your video or bio.');
                                        });
                                    };
                                    shareModal.show();
                                });
                            });
                            copyBtn.addEventListener('click', function() {
                                navigator.clipboard.writeText(shareUrlInput.value).then(function() {
                                    copyBtn.textContent = 'Copied!';
                                    setTimeout(function() { copyBtn.textContent = 'Copy Link'; }, 1500);
                                });
                            });
                            // Redirect to main page after closing the share modal
                            shareModalEl.addEventListener('hidden.bs.modal', function () {
                                window.location.href = window.location.pathname;
                            });
                        });
                        </script>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End single post card -->

            <!-- Remove inline comment box, handled by modal below -->
<!-- Comment Modal (single, reused for all posts) -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Post & Comments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-2">
                    <img id="commentModalAvatar" src="" class="rounded-circle me-2 object-fit-cover" width="36" height="36" alt="Profile picture" onerror="this.onerror=null;this.src='../../assets/images/profile/user-1.jpg';" />
                    <div class="flex-grow-1">
                        <span id="commentModalUser" class="fw-bold"></span>
                        <div class="text-muted small" id="commentModalDate"></div>
                    </div>
                </div>
                <div class="mb-2" id="commentModalContent"></div>
                <div id="commentModalMedia"></div>
                <hr />
                <div id="commentModalComments">
                    <div class="text-center text-muted">Loading comments...</div>
                </div>
                <div id="commentModalFormContainer"></div>
            </div>
        </div>
    </div>
</div>
<script>
// Comment modal logic
document.addEventListener('DOMContentLoaded', function() {
    var commentModalEl = document.getElementById('commentModal');
    var commentModal = new bootstrap.Modal(commentModalEl);
    var avatarEl = document.getElementById('commentModalAvatar');
    var userEl = document.getElementById('commentModalUser');
    var dateEl = document.getElementById('commentModalDate');
    var contentEl = document.getElementById('commentModalContent');
    var commentsEl = document.getElementById('commentModalComments');
    var formContainer = document.getElementById('commentModalFormContainer');

    // Refresh page when comment modal is closed
    commentModalEl.addEventListener('hidden.bs.modal', function () {
        window.location.reload();
    });

    document.querySelectorAll('.comment-modal-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Set post info
            avatarEl.src = btn.getAttribute('data-post-avatar');
            userEl.textContent = btn.getAttribute('data-post-user');
            dateEl.textContent = btn.getAttribute('data-post-date');
            contentEl.innerHTML = btn.getAttribute('data-post-content');
            // Load post media (images/videos) via AJAX
            var postId = btn.getAttribute('data-post-id');
            var mediaEl = document.getElementById('commentModalMedia');
            mediaEl.innerHTML = '';
            fetch('../api/get_post_media.php?post_id=' + encodeURIComponent(postId))
                .then(r => r.json())
                .then(data => {
                    var html = '';
                    if (data.photos && data.photos.length > 0) {
                        if (data.photos.length === 1) {
                            html += `<div class=\"post-media-single mb-3\"><img src=\"${data.photos[0]}\" alt=\"Post photo\" style=\"max-width:100%;height:auto;object-fit:contain;\" /></div>`;
                        } else {
                            html += '<div class="row row-cols-2 row-cols-md-2 g-2 mb-3">';
                            data.photos.slice(0,4).forEach(function(photo, idx) {
                                html += `<div class=\"col\"><div style=\"position:relative; padding-top:100%; overflow:hidden;\"><img src=\"${photo}\" class=\"position-absolute top-0 start-0 w-100 h-100 object-fit-cover rounded\" /></div></div>`;
                            });
                            html += '</div>';
                        }
                    }
                    if (data.videos && data.videos.length > 0) {
                        data.videos.forEach(function(video) {
                            html += `<div class=\"video-container position-relative mb-3\"><video controls class=\"w-100\" style=\"max-height:420px; aspect-ratio:1; object-fit:cover;\" controlslist=\"nodownload\"><source src=\"${video}\" /></video></div>`;
                        });
                    }
                    mediaEl.innerHTML = html;
                });
            // Load comments via AJAX
            commentsEl.innerHTML = '<div class="text-center text-muted">Loading comments...</div>';
            fetch('../api/get_post_comments.php?post_id=' + encodeURIComponent(postId))
                .then(r => r.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        commentsEl.innerHTML = data.map(function(c) {
                            return `<div class=\"mb-2\"><b>${c.full_name}</b> <span class=\"text-muted small\">${c.created_at}</span><div>${c.content}</div></div>`;
                        }).join('');
                    } else {
                        commentsEl.innerHTML = '<div class="text-muted">No comments yet.</div>';
                    }
                })
                .catch(() => { commentsEl.innerHTML = '<div class="text-danger">Failed to load comments.</div>'; });
            // Show comment form if logged in
            <?php if ($userId): ?>
            formContainer.innerHTML = `
                <form id=\"commentModalForm\" class=\"d-flex align-items-start gap-2 mt-3\" autocomplete=\"off\">
                    <img src=\"<?php echo htmlspecialchars($composerAvatar, ENT_QUOTES, 'UTF-8'); ?>\" class=\"rounded-circle object-fit-cover\" width=\"32\" height=\"32\" alt=\"Your avatar\" style=\"object-fit:cover; aspect-ratio:1/1;\" onerror=\"this.onerror=null;this.src='../../assets/images/profile/user-1.jpg';\">
                    <div class=\"flex-grow-1\">
                        <input type=\"hidden\" name=\"action\" value=\"create\">
                        <input type=\"hidden\" name=\"post_id\" value=\"${postId}\">
                        <div class=\"position-relative\">
                            <input type=\"text\" name=\"content\" class=\"form-control pe-5\" placeholder=\"Write a comment...\" required maxlength=\"1000\">
                            <button type=\"submit\" class=\"btn btn-primary position-absolute top-50 end-0 translate-middle-y me-2 p-0 d-inline-flex align-items-center justify-content-center\" style=\"width:32px; height:32px; border-radius:50%;\" aria-label=\"Send comment\">
                                <i class=\"ti ti-send\"></i>
                            </button>
                        </div>
                    </div>
                </form>
            `;
            // AJAX submit for comment form
            setTimeout(function() {
                var commentForm = document.getElementById('commentModalForm');
                if (commentForm) {
                    commentForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        var submitBtn = commentForm.querySelector('button[type="submit"]');
                        if (submitBtn) submitBtn.disabled = true;
                        var formData = new FormData(commentForm);
                        fetch('../controllers/AddCommentController.php', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.comment) {
                                // Reload comments from server to avoid duplicates
                                commentsEl.innerHTML = '<div class="text-center text-muted">Loading comments...</div>';
                                fetch('../api/get_post_comments.php?post_id=' + encodeURIComponent(postId))
                                    .then(r => r.json())
                                    .then(data => {
                                        if (Array.isArray(data) && data.length > 0) {
                                            commentsEl.innerHTML = data.map(function(c) {
                                                return `<div class=\"mb-2\"><b>${c.full_name}</b> <span class=\"text-muted small\">${c.created_at}</span><div>${c.content}</div></div>`;
                                            }).join('');
                                        } else {
                                            commentsEl.innerHTML = '<div class="text-muted">No comments yet.</div>';
                                        }
                                    })
                                    .catch(() => { commentsEl.innerHTML = '<div class="text-danger">Failed to load comments.</div>'; });
                                commentForm.reset();
                            } else {
                                alert(data.error || 'Comment Added.');
                            }
                        })
                        .catch(() => { alert('Comment Added.'); })
                        .finally(() => {
                            if (submitBtn) submitBtn.disabled = false;
                        });
                    });
                }
            }, 100);
            <?php else: ?>
            formContainer.innerHTML = `<div class=\"alert alert-info mt-3\">Please <a href=\"../login.php\" class=\"alert-link\">log in</a> to comment.</div>`;
            <?php endif; ?>
            commentModal.show();
        });
    });
});
</script>
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