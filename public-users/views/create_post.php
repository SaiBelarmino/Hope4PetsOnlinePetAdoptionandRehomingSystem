<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../controllers/create-post-controller.php';
SessionManager::requireLogin();

/**
 * View: create_post.php
 * Tables: posts, post_photos, pets (optional association)
 * Expected Variables:
 *  - $pets (optional) => user pets list for attaching to a post
 *  - $flash (optional)
 */

// Check if we're editing an existing post
$editMode = false;
$editPost = null;
$editPostId = 0;

if (isset($_GET['edit']) && (int)$_GET['edit'] > 0) {
    $editPostId = (int)$_GET['edit'];
    $userId = SessionManager::getUserId();
    $editPost = PublicCreatePostController::getPostForEdit($editPostId, $userId);
    
    if ($editPost) {
        $editMode = true;
    } else {
        SessionManager::setFlash('error', 'Post not found or you do not have permission to edit it.');
        header('Location: ./index.php');
        exit;
    }
}

$pageTitle = $editMode ? 'Edit Post' : 'Create Post';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="<?php echo $editMode ? './post_view.php?id='.$editPostId : './index.php'; ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left"></i> Back</a>
      </div>
      <?php 
      $flash = SessionManager::getFlash();
      if(!empty($flash['message'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type'] ?? 'info'); ?> alert-dismissible fade show">
          <?php echo htmlspecialchars($flash['message']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <div class="card">
        <div class="card-body">
          <form action="../controllers/create-post-controller.php" method="post" enctype="multipart/form-data">
            <?php if ($editMode): ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="post_id" value="<?php echo $editPostId; ?>">
            <?php endif; ?>
            <div class="mb-3">
              <label class="form-label">Content</label>
              <textarea name="content" class="form-control" rows="4" placeholder="What's new?" required><?php echo $editMode ? htmlspecialchars($editPost['content']) : ''; ?></textarea>
            </div>
            <?php if ($editMode && !empty($editPost['photos'])): ?>
            <div class="mb-3">
              <label class="form-label">Current Photos</label>
              <div class="row g-2">
                <?php 
                $currentPhotos = explode(',', $editPost['photos']);
                foreach($currentPhotos as $photo): ?>
                  <div class="col-4 col-md-3">
                    <img src="../../<?php echo htmlspecialchars(trim($photo)); ?>" class="img-fluid rounded" alt="Post photo">
                  </div>
                <?php endforeach; ?>
              </div>
              <small class="text-muted">Note: You can add new photos below. To remove existing photos, please delete and recreate the post.</small>
            </div>
            <?php endif; ?>
            <div class="mb-3">
              <label class="form-label"><?php echo $editMode ? 'Add New Photos' : 'Attach Photos'; ?></label>
              <div id="dropArea" class="border border-2 border-primary rounded p-3 text-center mb-2 bg-light" style="cursor:pointer; min-height: 100px;">
                <span id="dropText">Drag & drop images here or click to select</span>
                <input type="file" name="photos[]" id="photoInput" class="form-control d-none" multiple accept="image/*">
              </div>
              <div id="photoPreview" class="row g-2 mt-2"></div>
              <small class="text-muted">You can upload up to 5 images. Each max 5MB.</small>
              <div id="photoLimitWarning" class="text-danger small mt-1" style="display:none;"></div>

              <!-- Preview Script image drag and drop -->
              <script>
              const dropArea = document.getElementById('dropArea');
              const photoInput = document.getElementById('photoInput');
              const preview = document.getElementById('photoPreview');
              const dropText = document.getElementById('dropText');
              dropArea.addEventListener('click', () => photoInput.click());
              dropArea.addEventListener('dragover', e => {
                e.preventDefault();
                dropArea.classList.add('bg-primary','text-white');
                dropText.textContent = 'Release to upload images';
              });
              dropArea.addEventListener('dragleave', e => {
                e.preventDefault();
                dropArea.classList.remove('bg-primary','text-white');
                dropText.textContent = 'Drag & drop images here or click to select';
              });
              dropArea.addEventListener('drop', e => {
                e.preventDefault();
                dropArea.classList.remove('bg-primary','text-white');
                dropText.textContent = 'Drag & drop images here or click to select';
                let files = Array.from(e.dataTransfer.files);
                if (files.length > 5) {
                  document.getElementById('photoLimitWarning').textContent = 'You can only upload up to 5 images.';
                  document.getElementById('photoLimitWarning').style.display = '';
                  files = files.slice(0, 5);
                } else {
                  document.getElementById('photoLimitWarning').style.display = 'none';
                }
                // Create a DataTransfer to set the limited files
                const dt = new DataTransfer();
                files.forEach(f => dt.items.add(f));
                photoInput.files = dt.files;
                previewImages({target: photoInput});
              });
              photoInput.addEventListener('change', function(e) {
                let files = Array.from(photoInput.files);
                if (files.length > 5) {
                  document.getElementById('photoLimitWarning').textContent = 'You can only upload up to 5 images.';
                  document.getElementById('photoLimitWarning').style.display = '';
                  // Keep only the first 5
                  const dt = new DataTransfer();
                  files.slice(0, 5).forEach(f => dt.items.add(f));
                  photoInput.files = dt.files;
                } else {
                  document.getElementById('photoLimitWarning').style.display = 'none';
                }
                previewImages(e);
              });
              function previewImages(event) {
                preview.innerHTML = '';
                const files = event.target.files;
                if (!files) return;
                Array.from(files).forEach(file => {
                  if (!file.type.startsWith('image/')) return;
                  const reader = new FileReader();
                  reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-4 col-md-3';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-fluid rounded';
                    img.alt = 'Preview';
                    col.appendChild(img);
                    preview.appendChild(col);
                  };
                  reader.readAsDataURL(file);
                });
              }
              </script>
              <!-- End Preview Script -->
              
            </div>
            <div class="mb-3">
              <label class="form-label">Link a Pet (optional)</label>
              <select name="pet_id" class="form-select">
                <option value="">None</option>
                <?php 
                // Fetch user's pets
                $userId = SessionManager::getUserId();
                $pets = PublicCreatePostController::fetchAll(
                  "SELECT id, name, species FROM pets WHERE user_id = ? OR shelter_id IN (SELECT id FROM shelters WHERE user_id = ?)",
                  'ii',
                  [$userId, $userId]
                );
                if(!empty($pets)) foreach($pets as $p): 
                  $selected = ($editMode && $editPost['pet_id'] == $p['id']) ? 'selected' : '';
                ?>
                  <option value="<?php echo (int)$p['id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($p['name'].' • '.ucfirst($p['species'])); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="d-flex justify-content-end gap-2">
              <a href="<?php echo $editMode ? './post_view.php?id='.$editPostId : './index.php'; ?>" class="btn btn-light border">Cancel</a>
              <button class="btn btn-primary">
                <i class="ti ti-<?php echo $editMode ? 'check' : 'send'; ?>"></i> 
                <?php echo $editMode ? 'Update' : 'Post'; ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Tips</h6>
          <p class="small text-muted mb-0">Attach clear photos. Link a pet to boost visibility.</p>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./community.php" class="btn btn-sm btn-light border">Community</a>
            <a href="./my_posts.php" class="btn btn-sm btn-light border">My Posts</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
