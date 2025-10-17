<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<link rel="stylesheet" href="./assets/css/postview.css">

<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../controllers/PostManagementController.php';

// Get post id from query
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$photos = [];
$videos = [];
$comments = [];

if ($postId > 0) {
    $post = PostManagementController::getPost($postId);
    if ($post) {
        $photos = PostManagementController::getPostPhotos($postId);
        $videos = PostManagementController::getPostVideos($postId);
        $comments = PostManagementController::getComments($postId);
    }
}

// Build a combined items array (photos first, then videos) for the left-side carousel
$items = [];
if (!empty($photos)) {
    foreach ($photos as $p) {
        $items[] = ['type' => 'photo', 'path' => $p['photo_path'] ?? $p['path'] ?? ''];
    }
}
if (!empty($videos)) {
    foreach ($videos as $v) {
        $items[] = ['type' => 'video', 'path' => $v['video_path'] ?? $v['path'] ?? ''];
    }
}

// helper to resolve media URLs (reuse earlier logic if available)
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
    $normalized = preg_replace('#^(\\.{1,2}/)+#', '', $normalized);
    $normalized = ltrim($normalized, '/');
    if (stripos($normalized, 'storage/') === 0) return '../../' . $normalized;
    if (stripos($normalized, 'uploads/') === 0) return '../../storage/' . ltrim($normalized, '/');
    return '../../' . $normalized;
}

?>

<div class="d-flex post-root" style="height: calc(100vh - 70px); overflow: hidden;">
    <!-- LEFT SIDE: MEDIA -->
    <div class="flex-grow-1 d-flex align-items-center justify-content-center bg-black post-media">
        <?php if (!$post): ?>
            <div class="text-white">Post not found.</div>
        <?php else: ?>
            <?php if (empty($items)): ?>
                <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                    <img src="<?php echo htmlspecialchars(resolve_media_path(''), ENT_QUOTES, 'UTF-8'); ?>" class="d-block" style="max-width:100%; max-height:100%; object-fit:contain;">
                </div>
            <?php elseif (count($items) === 1): ?>
                <?php $it = $items[0]; ?>
                <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                    <?php if ($it['type'] === 'photo'): ?>
                        <img src="<?php echo htmlspecialchars(resolve_media_path($it['path']), ENT_QUOTES, 'UTF-8'); ?>" class="d-block" style="max-width:100%; max-height:100%; object-fit:contain;">
                    <?php else: ?>
                        <video controls style="max-width:100%; max-height:100%; object-fit:contain; background:black;">
                            <source src="<?php echo htmlspecialchars(resolve_media_path($it['path']), ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div id="postMediaCarousel" class="carousel slide carousel-fade w-100 h-100">
                    <div class="carousel-inner h-100">
                        <?php foreach ($items as $idx => $it): ?>
                            <div class="carousel-item <?php echo ($idx === 0) ? 'active' : ''; ?> h-100 d-flex align-items-center justify-content-center">
                                <?php if ($it['type'] === 'photo'): ?>
                                    <img src="<?php echo htmlspecialchars(resolve_media_path($it['path']), ENT_QUOTES, 'UTF-8'); ?>" class="d-block w-100 h-100" style="object-fit:contain; max-height:100%;">
                                <?php else: ?>
                                    <video controls class="w-100 h-100" style="object-fit:contain; max-height:100%; background:black;">
                                        <source src="<?php echo htmlspecialchars(resolve_media_path($it['path']), ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#postMediaCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#postMediaCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- RIGHT SIDE: POST DETAILS -->
    <div class="d-flex flex-column bg-white border-start post-side"
        style="width: 420px; max-width: 100%; height: calc(100vh - 70px);">

        <!-- Header -->
        <div class="p-3 border-bottom d-flex align-items-center">
            <img src="<?php echo htmlspecialchars($post['profile_photo'] ?? 'https://via.placeholder.com/40', ENT_QUOTES, 'UTF-8'); ?>" alt="Profile" class="rounded-circle me-2"
                style="width: 40px; height: 40px; object-fit:cover;">
            <div>
                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($post['full_name'] ?? 'Unknown'); ?></h6>
                <small class="text-muted"><?php echo isset($post['created_at']) ? htmlspecialchars((new DateTime($post['created_at']))->format('M d, Y H:i')) : ''; ?> · <i class="ti ti-world"></i></small>
            </div>
            <i class="ti ti-dots ms-auto"></i>
        </div>

        <!-- Caption -->
        <div class="p-3 border-bottom" id="captionContainer">
            <div id="captionText" class="small position-relative" style="max-height: 120px; overflow: hidden;">
                <?php if (!empty($post['content'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php endif; ?>
                <!-- Fade overlay -->
                <div id="fadeOverlay"
                    style="position:absolute; bottom:0; left:0; right:0; height:40px; background: linear-gradient(transparent, white); display:block; pointer-events: none; z-index:2;">
                </div>
            </div>
            <!-- See more / See less button placed outside the collapsed area so it's always visible -->
            <a href="javascript:void(0)" id="seeMoreBtn" class="text-decoration-none small fw-bold d-block mt-2"
                style="position:relative; z-index:3; color:#0d6efd;">See more</a>
        </div>

        <!-- Comments -->
        <div class="px-3 py-2 flex-grow-1 overflow-auto comments-scroll">
            <?php
            if (empty($comments)) {
                echo '<div class="text-muted small">No comments yet.</div>';
            } else {
                foreach ($comments as $comment) {
                    echo '<div class="d-flex mb-3 align-items-start">';
                    echo '<img src="' . htmlspecialchars((!empty($comment['profile_photo']) ? resolve_media_path($comment['profile_photo']) : 'https://via.placeholder.com/30'), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($comment['full_name']) . '" class="rounded-circle me-2" style="width:30px;height:30px; object-fit:cover;">';
                    echo '<div class="w-100">';
                    echo '<div class="bg-light rounded-3 px-2 py-1 d-flex justify-content-between align-items-start">';
                    echo '<div><strong>' . htmlspecialchars($comment['full_name']) . ':</strong> ' . htmlspecialchars($comment['comment_text']) . '</div>';
                    // action buttons for owner
                    $own = (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $comment['user_id']);
                    if ($own) {
                        echo '<div class="ms-2">';
                        echo '<form method="post" action="../controllers/DeleteCommentController.php" style="display:inline;">
                                <input type="hidden" name="comment_id" value="' . htmlspecialchars($comment['id']) . '">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-1">Delete</button>
                            </form>';
                        echo '<a href="#" class="btn btn-sm btn-link p-0 ms-1 edit-comment-link" data-comment-id="' . htmlspecialchars($comment['id']) . '">Edit</a>';
                        echo '</div>';
                    }
                    echo '</div>'; // end comment bubble
                    // edit form hidden
                    if ($own) {
                        echo '<form method="post" action="../controllers/EditCommentController.php" class="mt-1 edit-comment-form" data-comment-id="' . htmlspecialchars($comment['id']) . '" style="display:none;">
                                <input type="hidden" name="comment_id" value="' . htmlspecialchars($comment['id']) . '">
                                <div class="input-group">
                                    <input type="text" name="comment_text" class="form-control form-control-sm" value="' . htmlspecialchars($comment['comment_text']) . '">
                                    <button class="btn btn-primary btn-sm" type="submit">Save</button>
                                    <button class="btn btn-secondary btn-sm cancel-edit" type="button">Cancel</button>
                                </div>
                            </form>';
                    }
                    echo '<div class="ms-2"><a href="#" class="small text-muted me-2 text-decoration-none">Like</a><a href="#" class="small text-muted text-decoration-none">Reply</a></div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- Comment Input -->
        <div class="border-top p-3">
            <form method="post" action="../controllers/AddCommentController.php">
                <div class="d-flex align-items-center">
                    <img src="<?php echo htmlspecialchars($_SESSION['user']['profile_photo'] ?? 'https://via.placeholder.com/30', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-circle me-2"
                        style="width:30px;height:30px; object-fit:cover;">
                    <input type="text" name="comment_text"
                        class="form-control form-control-sm rounded-pill bg-light border-0"
                        placeholder="Write a comment..." required>
                </div>
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($postId); ?>">
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>var postId = <?php echo json_encode($postId); ?>;</script>
<script src="./assets/js/postview.js"></script>
