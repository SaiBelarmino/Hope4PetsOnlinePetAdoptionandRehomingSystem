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
      <?php 
      // Display flash messages
      $flash = SessionManager::getFlash();
      if (!empty($flash['message'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type'] ?? 'info'); ?> alert-dismissible fade show" id="autoHideAlert">
          <?php echo htmlspecialchars($flash['message']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
        // Auto-hide alert after 3 seconds
        setTimeout(function() {
          var alert = document.getElementById('autoHideAlert');
          if (alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
          }
        }, 3000); // 3 seconds
        </script>
      <?php endif; ?>
      <div class="card mb-3">
        <div class="card-body">
          <?php if(!$post): ?>
            <div class="alert alert-warning mb-0">Post not found or has been removed.</div>
          <?php else: ?>
          <?php 
            // Get profile photo or default (handles Google URLs)
            $profilePhoto = resolve_profile_photo($post['profile_photo'] ?? null);
            
            // Check if current user is the post owner
            $isOwner = isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $post['user_id'];
            
            // DEBUG: Remove this after testing
            // echo "Session User ID: " . ($_SESSION['user']['id'] ?? 'not set') . "<br>";
            // echo "Post User ID: " . ($post['user_id'] ?? 'not set') . "<br>";
            // echo "Is Owner: " . ($isOwner ? 'YES' : 'NO') . "<br>";
          ?>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
              <a href="./profile.php?user_id=<?php echo (int)$post['user_id']; ?>" class="text-decoration-none">
                <img src="<?php echo $profilePhoto; ?>" 
                     class="rounded-circle me-2" 
                     width="44" height="44" 
                     alt="User"
                     style="cursor: pointer; transition: opacity 0.2s;"
                     onmouseover="this.style.opacity='0.8'"
                     onmouseout="this.style.opacity='1'"
                     onerror="this.src='../../assets/images/profile/user-1.jpg'">
              </a>
              <div>
                <a href="./profile.php?user_id=<?php echo (int)$post['user_id']; ?>" class="text-dark text-decoration-none">
                  <strong style="cursor: pointer;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                    <?php echo htmlspecialchars($post['user_name'] ?? ('User #'.$post['user_id'])); ?>
                  </strong>
                </a><br>
                <span class="text-muted small"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($post['created_at'] ?? 'now'))); ?></span>
              </div>
            </div>
            <!-- ALWAYS SHOW BUTTON FOR TESTING - REMOVE CONDITION TEMPORARILY -->
            <div class="dropdown position-relative">
              <button type="button" class="btn btn-sm btn-light border-0 p-2" id="postMenuBtn<?php echo (int)$post['id']; ?>" onclick="togglePostMenu(<?php echo (int)$post['id']; ?>)">
                <i class="ti ti-dots fs-5"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end shadow" id="postMenu<?php echo (int)$post['id']; ?>" style="display: none; position: absolute; right: 0; top: 100%; z-index: 1000; min-width: 150px;">
                <a class="dropdown-item" href="./create_post.php?edit=<?php echo (int)$post['id']; ?>">
                  <i class="ti ti-edit me-2"></i>Edit Post
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this post?')) { document.getElementById('deletePostForm<?php echo (int)$post['id']; ?>').submit(); }">
                  <i class="ti ti-trash me-2"></i>Delete Post
                </a>
              </div>
            </div>
            <form id="deletePostForm<?php echo (int)$post['id']; ?>" action="../controllers/create-post-controller.php" method="post" style="display:none;">
              <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
              <input type="hidden" name="action" value="delete">
            </form>
            <script>
            function togglePostMenu(postId) {
              var menu = document.getElementById('postMenu' + postId);
              if (menu.style.display === 'none' || menu.style.display === '') {
                // Hide all other menus first
                document.querySelectorAll('.dropdown-menu').forEach(function(m) {
                  m.style.display = 'none';
                });
                menu.style.display = 'block';
              } else {
                menu.style.display = 'none';
              }
            }
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
              if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(function(m) {
                  m.style.display = 'none';
                });
              }
            });
            </script>
          </div>
          <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'] ?? '')); ?></p>
          <?php if(!empty($photos)): ?>
            <div class="row g-2 mb-2">
               <?php foreach($photos as $ph): ?>
                <div class="col-6 col-md-4">
                  <div class="ratio ratio-4x3 bg-light rounded overflow-hidden">
                    <img src="../../<?php echo htmlspecialchars($ph['photo_path']); ?>" 
                         class="object-fit-cover" 
                         alt="Post photo"
                         onerror="this.src='../../assets/images/profile/user-placeholder.png'">
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php 
            // Fetch video if exists
            $videoRow = PostViewController::video($postId);
            if (!empty($videoRow) && !empty($videoRow['video_path'])): 
          ?>
            <div class="mb-3">
              <video controls class="w-100 rounded" style="max-height:480px; background:#000;">
                <source src="../../<?php echo htmlspecialchars($videoRow['video_path']); ?>" type="video/mp4">
                Your browser does not support the video tag.
              </video>
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
          <?php else: foreach($comments as $c): 
            $commentProfilePhoto = resolve_profile_photo($c['profile_photo'] ?? null);
          ?>
            <div class="mb-3 border-bottom pb-2">
              <div class="d-flex align-items-start">
                <a href="./profile.php?user_id=<?php echo (int)$c['user_id']; ?>" class="text-decoration-none">
                  <img src="<?php echo htmlspecialchars($commentProfilePhoto, ENT_QUOTES, 'UTF-8'); ?>" 
                       class="rounded-circle me-2" 
                       width="32" height="32" 
                       alt="User"
                       style="cursor: pointer; transition: opacity 0.2s;"
                       onmouseover="this.style.opacity='0.8'"
                       onmouseout="this.style.opacity='1'"
                       onerror="this.src='../../assets/images/profile/user-1.jpg'">
                </a>
                <div class="flex-grow-1">
                  <div class="small">
                    <a href="./profile.php?user_id=<?php echo (int)$c['user_id']; ?>" class="fw-semibold text-dark text-decoration-none" style="cursor: pointer;">
                      <span onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                        <?php echo htmlspecialchars($c['user_name'] ?? ('User #'.$c['user_id'])); ?>
                      </span>
                    </a>
                    <span class="text-muted fw-normal">• <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($c['created_at']))); ?></span>
                  </div>
                  <div><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
                </div>
              </div>
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
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
