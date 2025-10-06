<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../controllers/post-view-controller.php';
SessionManager::requireLogin();

// Bootstrap fetch
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = $postId ? PostViewController::get($postId) : null;
if (!$post) {
  // Early simple not found output (still include header for layout)
  $pageTitle = 'Post Not Found';
  $photos = $comments = [];
  $reactionCount = 0; $userReacted = false;
} else {
  $photos = PostViewController::photos($postId);
  $comments = PostViewController::comments($postId);
  $reactionCount = (int)($post['reaction_count'] ?? 0);
  $userReacted = PostViewController::userReacted($postId, SessionManager::getUserId());
  $pageTitle = 'Post';
}
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Navigate</h6>
          <div class="d-grid gap-2">
            <a href="./community.php" class="btn btn-sm btn-outline-secondary">Community</a>
            <a href="./create_post.php" class="btn btn-sm btn-outline-primary">Create Post</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">Post</h3>
        <a href="./index.php" class="btn btn-outline-secondary"><i class="ti ti-arrow-left"></i> Feed</a>
      </div>
      <div class="card mb-3">
        <div class="card-body">
      <div class="card mb-3">
        <div class="card-body">
          <?php if(!$post): ?>
            <div class="alert alert-warning mb-0">Post not found or has been removed.</div>
          <?php else: ?>
          <div class="d-flex align-items-center mb-2">
            <img src="../../assets/images/profile/user-placeholder.png" class="rounded-circle me-2" width="44" height="44" alt="User">
            <div>
              <strong><?php echo htmlspecialchars($post['user_name'] ?? ('User #'.$post['user_id'])); ?></strong><br>
              <span class="text-muted small"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($post['created_at'] ?? 'now'))); ?></span>
            </div>
          </div>
          <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'] ?? '')); ?></p>
          <?php if(!empty($photos)): ?>
            <div class="row g-2 mb-2">
               <?php foreach($photos as $ph): ?>
                 <?php 
                   $rawPath = $ph['photo_path'];
                   // Normalize to /storage/... web path
                   $norm = str_replace('\\', '/', $rawPath); // Windows backslash to forward
                   // If absolute Windows path (C:/ or similar), extract from 'storage' onward
                   if (preg_match('/^[A-Za-z]:\//', $norm)) {
                       $pos = stripos($norm, 'storage/');
                       if ($pos !== false) { $norm = substr($norm, $pos); }
                   }
                   // Ensure leading slash for web path
                   if (strpos($norm, 'storage/') === 0) { $norm = '/' . $norm; }
                   $imgPath = $norm ?: '';
                 ?>
                <div class="col-6 col-md-4">
                  <div class="ratio ratio-4x3 bg-light rounded overflow-hidden">
                    <img src="<?php echo htmlspecialchars($imgPath); ?>" class="object-fit-cover" alt="Post photo">
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($post['pet_id'])): ?>
            <div class="alert alert-light border d-flex justify-content-between align-items-center">
              <div class="small"><strong>Pet Featured:</strong> <a href="./pet_view.php?id=<?php echo (int)$post['pet_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($post['pet_name']); ?></a></div>
              <a href="./adopt.php?pet_id=<?php echo (int)$post['pet_id']; ?>" class="btn btn-sm btn-primary"><i class="ti ti-heart"></i> Adopt</a>
            </div>
          <?php endif; ?>
          <div class="d-flex gap-2 mt-3">
            <form action="../controllers/post-view-controller.php" method="post">
              <input type="hidden" name="post_id" value="<?php echo (int)($post['id'] ?? 0); ?>">
              <input type="hidden" name="action" value="toggle_like">
              <button class="btn btn-sm <?php echo !empty($userReacted)?'btn-danger':'btn-outline-danger'; ?>"><i class="ti ti-thumb-up"></i> <?php echo (int)($reactionCount ?? 0); ?></button>
            </form>
            <a href="#comments" class="btn btn-sm btn-outline-secondary"><i class="ti ti-message-circle"></i> <?php echo count($comments ?? []); ?></a>
          </div>
          <?php endif; ?>
        </div>
      </div>
  <?php if($post): ?>
  <div class="card" id="comments">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Comments (<?php echo count($comments ?? []); ?>)</h6></div>
        <div class="card-body">
          <?php if (empty($comments)): ?>
            <div class="text-muted small">No comments yet.</div>
          <?php else: foreach($comments as $c): ?>
            <div class="mb-3 border-bottom pb-2">
              <div class="small fw-semibold"><?php echo htmlspecialchars($c['user_name'] ?? ('User #'.$c['user_id'])); ?> <span class="text-muted fw-normal">• <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($c['created_at']))); ?></span></div>
              <div><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
            </div>
          <?php endforeach; endif; ?>
          <form action="../controllers/post-view-controller.php" method="post" class="mt-3">
            <input type="hidden" name="post_id" value="<?php echo (int)($post['id'] ?? 0); ?>">
            <div class="mb-2"><textarea class="form-control" name="comment" rows="2" placeholder="Add a comment..."></textarea></div>
            <div class="d-flex justify-content-end"><button class="btn btn-sm btn-primary"><i class="ti ti-message"></i> Post</button></div>
          </form>
        </div>
      </div>
  <?php endif; ?>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Actions</h6></div>
        <div class="card-body small">
          <a href="./create_post.php?edit=<?php echo (int)($post['id'] ?? 0); ?>" class="btn btn-sm btn-outline-secondary w-100 mb-2"><i class="ti ti-edit"></i> Edit</a>
          <form action="../controllers/create-post-controller.php" method="post" onsubmit="return confirm('Delete this post?');">
            <input type="hidden" name="post_id" value="<?php echo (int)($post['id'] ?? 0); ?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn btn-sm btn-outline-danger w-100"><i class="ti ti-trash"></i> Delete</button>
          </form>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./community.php" class="btn btn-sm btn-light border">Community Feed</a>
            <a href="./my_posts.php" class="btn btn-sm btn-light border">My Posts</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
