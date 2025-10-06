<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: pet_view.php
 * Tables: pets, pet_photos, shelters, users, pet_comments, pet_reactions
 * Expected Variables:
 *  - $pet => ['id','name','species','breed','age','gender','size','vaccine_status','health_status','location','description','status','shelter_name','owner_name']
 *  - $photos => [ ['photo_path','is_primary'], ... ]
 *  - $comments => [ ['id','user_id','user_name','content','created_at'], ... ]
 *  - $userLiked (bool)
 *  - $reactionCount (int)
 */
$pageTitle = 'Pet Details';
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
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Browse Pets</a>
            <a href="./donate.php" class="btn btn-sm btn-outline-primary">Donate</a>
            <a href="./my_adoptions.php" class="btn btn-sm btn-outline-secondary">My Adoptions</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pet['name'] ?? $pageTitle); ?></h3>
        <div class="d-flex gap-2">
          <?php if (!empty($pet['status']) && $pet['status']==='available'): ?>
            <a href="./adopt.php?pet_id=<?php echo (int)$pet['id']; ?>" class="btn btn-primary"><i class="ti ti-heart"></i> Adopt</a>
          <?php endif; ?>
          <a href="./pets.php" class="btn btn-outline-secondary"><i class="ti ti-arrow-left"></i> Back</a>
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-12">
              <div class="ratio ratio-4x3 bg-light rounded overflow-hidden mb-2">
                <?php $primary = null; if(!empty($photos)){ foreach($photos as $ph){ if($ph['is_primary']){ $primary=$ph; break; } } if(!$primary) $primary=$photos[0]; } ?>
                <?php if($primary): ?>
                  <img src="<?php echo htmlspecialchars($primary['photo_path']); ?>" class="object-fit-cover" alt="Primary photo">
                <?php else: ?><div class="d-flex align-items-center justify-content-center text-muted">No photo</div><?php endif; ?>
              </div>
              <?php if (!empty($photos) && count($photos)>1): ?>
                <div class="d-flex flex-wrap gap-2">
                  <?php foreach($photos as $ph): if ($primary && $ph['photo_path']===$primary['photo_path']) continue; ?>
                    <div class="thumb border rounded" style="width:70px;height:70px;overflow:hidden;">
                      <img src="<?php echo htmlspecialchars($ph['photo_path']); ?>" class="object-fit-cover w-100 h-100" alt="Pet photo">
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">About</h6></div>
        <div class="card-body">
          <p class="mb-2"><?php echo nl2br(htmlspecialchars($pet['description'] ?? 'No description.')); ?></p>
          <div class="row g-2 small">
            <div class="col-6 col-md-4"><strong>Species:</strong> <?php echo htmlspecialchars($pet['species'] ?? ''); ?></div>
            <div class="col-6 col-md-4"><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed'] ?: 'Mixed'); ?></div>
            <div class="col-6 col-md-4"><strong>Age:</strong> <?php echo htmlspecialchars($pet['age'] ?: '?'); ?></div>
            <div class="col-6 col-md-4"><strong>Gender:</strong> <?php echo htmlspecialchars(ucfirst($pet['gender'] ?? '')); ?></div>
            <div class="col-6 col-md-4"><strong>Size:</strong> <?php echo htmlspecialchars(ucfirst($pet['size'] ?? '')); ?></div>
            <div class="col-6 col-md-4"><strong>Location:</strong> <?php echo htmlspecialchars($pet['location'] ?? '—'); ?></div>
            <div class="col-6 col-md-4"><strong>Vaccine:</strong> <?php echo htmlspecialchars($pet['vaccine_status'] ?? '—'); ?></div>
            <div class="col-6 col-md-4"><strong>Health:</strong> <?php echo htmlspecialchars($pet['health_status'] ?? '—'); ?></div>
            <div class="col-6 col-md-4"><strong>Status:</strong> <span class="badge bg-<?php echo ['available'=>'success','pending'=>'warning','adopted'=>'secondary','removed'=>'dark'][$pet['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($pet['status'] ?? '')); ?></span></div>
          </div>
        </div>
      </div>
  <div class="card mt-3" id="comments">
        <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Comments (<?php echo count($comments ?? []); ?>)</h6>
        </div>
        <div class="card-body">
          <?php if (empty($comments)): ?>
            <div class="text-muted small">No comments yet.</div>
          <?php else: foreach ($comments as $c): ?>
            <div class="mb-3 border-bottom pb-2">
              <div class="small fw-semibold"><?php echo htmlspecialchars($c['user_name'] ?? ('User #'.$c['user_id'])); ?> <span class="text-muted fw-normal">• <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($c['created_at']))); ?></span></div>
              <div><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
            </div>
          <?php endforeach; endif; ?>
          <form action="../controllers/pet-view-controller.php" method="post" class="mt-3">
            <input type="hidden" name="pet_id" value="<?php echo (int)($pet['id'] ?? 0); ?>">
            <div class="mb-2">
              <textarea class="form-control" name="comment" rows="2" placeholder="Add a comment..."></textarea>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-sm btn-primary"><i class="ti ti-message"></i> Post</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="small text-muted">Reactions</div>
            <h5 class="mb-0"><?php echo (int)($reactionCount ?? 0); ?></h5>
          </div>
          <form action="../controllers/pet-view-controller.php" method="post" class="m-0">
            <input type="hidden" name="pet_id" value="<?php echo (int)($pet['id'] ?? 0); ?>">
            <input type="hidden" name="action" value="toggle_like">
            <button class="btn btn-sm <?php echo !empty($userLiked)?'btn-danger':'btn-outline-danger'; ?>"><i class="ti ti-heart"></i> <?php echo !empty($userLiked)?'Liked':'Like'; ?></button>
          </form>
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Shelter / Owner</h6></div>
        <div class="card-body small">
          <p class="mb-1"><strong><?php echo htmlspecialchars($pet['shelter_name'] ?? $pet['owner_name'] ?? ''); ?></strong></p>
          <p class="text-muted mb-2">Contact / profile details could go here.</p>
          <a href="./shelter_view.php?id=<?php echo (int)($pet['shelter_id'] ?? 0); ?>" class="btn btn-sm btn-outline-primary" <?php if(empty($pet['shelter_name'])) echo 'disabled'; ?>>View Shelter</a>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">More</h6>
          <div class="d-grid gap-2">
            <a href="./donate.php" class="btn btn-sm btn-light border">Support Shelters</a>
            <a href="./pets.php" class="btn btn-sm btn-light border">All Pets</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
