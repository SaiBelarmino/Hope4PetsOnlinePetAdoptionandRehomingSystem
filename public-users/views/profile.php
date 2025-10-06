<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../controllers/profile-controller.php';
SessionManager::requireLogin();

/**
 * View: profile.php
 * Table: users, pets (counts), shelters (if user has), donations/adoptions aggregates optional
 * Expected Variables:
 *  - $user => ['id','full_name','birthday','gender','email','profile_photo','location','contact_number','is_verified','created_at']
 *  - $stats => ['pets'=>int,'adoptions'=>int,'donations'=>int]
 */

$viewUserId = null;
// Accept multiple query parameter names to be robust: user_id, id, u, uid
foreach (['user_id','id','u','uid'] as $param) {
  if (isset($_GET[$param]) && is_numeric($_GET[$param]) && (int)$_GET[$param] > 0) {
    $viewUserId = (int)$_GET[$param];
    break;
  }
}
if ($viewUserId === null) {
  $viewUserId = SessionManager::getUserId();
}
$currentUserId = SessionManager::getUserId();
$isOwnProfile = ($viewUserId === $currentUserId);

// Fetch user data
$user = ProfileController::get($viewUserId);

if (!$user) {
    $pageTitle = 'User Not Found';
    echo '<div class="alert alert-warning">User not found.</div>';
    exit;
}

// Fetch user stats and shelter
$stats = ProfileController::getStats($viewUserId);
$shelter = ProfileController::getShelter($viewUserId);
$posts = ProfileController::getPosts($viewUserId, 5);

$pageTitle = $isOwnProfile ? 'My Profile' : htmlspecialchars($user['full_name']) . "'s Profile";
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<?php
// Debug helper: when visiting /profile.php?user_id=NNN&debug=1 show stored values (remove in production)
if (!empty($_GET['debug'])) {
  echo '<div class="container"><pre style="background:#f8f9fa;padding:12px;border:1px solid #ddd">';
  echo "viewUserId: " . htmlspecialchars((string)$viewUserId) . "\n";
  echo "stored profile_photo: " . htmlspecialchars((string)($user['profile_photo'] ?? '<none>')) . "\n";
  $resolved = resolve_profile_photo($user['profile_photo'] ?? null);
  echo "resolved src: " . htmlspecialchars($resolved) . "\n";
  // If it's a storage path, check file existence on disk
  if (stripos($resolved, 'storage/') !== false || stripos($resolved, '../../storage/') !== false) {
    $checkPath = $resolved;
    // normalize to filesystem path
    $checkPath = preg_replace('#^\.\./\.\./#', '', $checkPath);
    $fsPath = __DIR__ . '/../../' . $checkPath;
    echo "filesystem path: " . htmlspecialchars($fsPath) . "\n";
    echo "file exists: " . (file_exists($fsPath) ? 'YES' : 'NO') . "\n";
  }
  echo '</pre></div>';
}
?>
<div class="pu-scroll-wrapper">
<div class="container-fluid py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><?php echo $pageTitle; ?></h3>
    <div class="d-flex gap-2">
      <?php if ($isOwnProfile): ?>
        <a href="./edit_profile.php" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i> Edit</a>
        <a href="./settings.php" class="btn btn-sm btn-outline-secondary"><i class="ti ti-settings"></i> Settings</a>
      <?php else: ?>
        <a href="./messages.php?u=<?php echo $viewUserId; ?>" class="btn btn-sm btn-primary"><i class="ti ti-message"></i> Message</a>
        <a href="./index.php" class="btn btn-sm btn-outline-secondary"><i class="ti ti-arrow-left"></i> Back</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body text-center">
          <div class="mb-3">
            <div class="ratio ratio-1x1 rounded-circle overflow-hidden bg-light mx-auto" style="width:140px;">
              <?php 
              // Resolve profile photo (handles Google URL or local paths)
              $profilePhotoPath = resolve_profile_photo($user['profile_photo'] ?? null);
              ?>
              <img src="<?php echo htmlspecialchars($profilePhotoPath, ENT_QUOTES, 'UTF-8'); ?>" 
                   class="object-fit-cover" 
                   alt="Profile"
                   onerror="this.src='../../assets/images/profile/user-1.jpg'">
            </div>
          </div>
          <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?> <?php if(!empty($user['is_verified'])): ?><span class="badge bg-primary">✔</span><?php endif; ?></h5>
          <p class="text-muted small mb-2">Member since <?php echo htmlspecialchars(date('M Y', strtotime($user['created_at'] ?? 'now'))); ?></p>
          <div class="d-flex flex-wrap justify-content-center gap-2 small mb-3">
            <span class="badge bg-light text-dark"><?php echo htmlspecialchars(ucfirst($user['gender'] ?? '')); ?></span>
            <?php if(!empty($user['location'])): ?><span class="badge bg-light text-dark"><?php echo htmlspecialchars($user['location']); ?></span><?php endif; ?>
          </div>
          <?php if (!$isOwnProfile): ?>
            <p class="small mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
          <?php else: ?>
            <p class="small mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
          <?php endif; ?>
          <p class="small mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($user['contact_number'] ?? '—'); ?></p>
          <p class="small mb-0"><strong>Birthday:</strong> <?php echo !empty($user['birthday'])? htmlspecialchars(date('M d, Y', strtotime($user['birthday']))):'—'; ?></p>
          
          <?php if ($shelter): ?>
          <hr class="my-3">
          <div class="text-start">
            <h6 class="mb-2"><i class="ti ti-home"></i> Shelter</h6>
            <p class="small mb-1"><strong><?php echo htmlspecialchars($shelter['shelter_name']); ?></strong></p>
            <p class="small text-muted mb-1"><?php echo htmlspecialchars($shelter['address'] ?? ''); ?></p>
            <br>
            <a href="./shelter_view.php?id=<?php echo (int)$shelter['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">View Shelter</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-8">
      <div class="row g-3 mb-1">
        <div class="col-4">
          <div class="card h-100"><div class="card-body text-center p-3">
            <h4 class="mb-0 text-primary"><?php echo (int)($stats['posts'] ?? 0); ?></h4>
            <small class="text-muted">Posts</small>
          </div></div>
        </div>
        <div class="col-4">
          <div class="card h-100"><div class="card-body text-center p-3">
            <h4 class="mb-0 text-success"><?php echo (int)($stats['adoptions'] ?? 0); ?></h4>
            <small class="text-muted">Adoptions</small>
          </div></div>
        </div>
        <div class="col-4">
          <div class="card h-100"><div class="card-body text-center p-3">
            <h4 class="mb-0 text-info"><?php echo (int)($stats['donations'] ?? 0); ?></h4>
            <small class="text-muted">Donations</small>
          </div></div>
        </div>
      </div>
      
      <!-- Recent Posts Section -->
      <div class="card mt-3">
        <div class="card-header bg-white">
          <h6 class="mb-0">Recent Posts</h6>
        </div>
        <div class="card-body">
          <?php if (empty($posts)): ?>
            <p class="text-muted small mb-0">No posts yet.</p>
          <?php else: ?>
            <?php foreach ($posts as $post): ?>
              <div class="border-bottom pb-2 mb-2">
                <p class="mb-1"><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 150))); ?><?php echo strlen($post['content']) > 150 ? '...' : ''; ?></p>
                <div class="d-flex justify-content-between align-items-center">
                  <small class="text-muted"><?php echo htmlspecialchars(date('M d, Y', strtotime($post['created_at']))); ?></small>
                  <div class="small text-muted">
                    <i class="ti ti-heart"></i> <?php echo (int)$post['reaction_count']; ?>
                    <i class="ti ti-message-circle ms-2"></i> <?php echo (int)$post['comment_count']; ?>
                  </div>
                </div>
                <a href="./post_view.php?id=<?php echo (int)$post['id']; ?>" class="btn btn-sm btn-outline-primary mt-1">View Post</a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
