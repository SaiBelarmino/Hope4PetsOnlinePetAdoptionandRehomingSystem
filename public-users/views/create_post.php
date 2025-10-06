<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: create_post.php
 * Tables: posts, post_photos, pets (optional association)
 * Expected Variables:
 *  - $pets (optional) => user pets list for attaching to a post
 *  - $flash (optional)
 */
$pageTitle = 'Create Post';
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
        <a href="./index.php" class="btn btn-outline-secondary"><i class="ti ti-arrow-left"></i> Feed</a>
      </div>
      <?php if(!empty($flash['message'])): ?><div class="alert alert-<?php echo htmlspecialchars($flash['type'] ?? 'info'); ?>"><?php echo htmlspecialchars($flash['message']); ?></div><?php endif; ?>
      <div class="card">
        <div class="card-body">
          <form action="../controllers/create-post-controller.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Content</label>
          <textarea name="content" class="form-control" rows="4" placeholder="What's new?" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Attach Photos</label>
          <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
          <small class="text-muted">You can upload up to 5 images. Each max 3MB.</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Link a Pet (optional)</label>
          <select name="pet_id" class="form-select">
            <option value="">None</option>
            <?php if(!empty($pets)) foreach($pets as $p): ?>
              <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name'].' • '.ucfirst($p['species'])); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <a href="./index.php" class="btn btn-light border">Cancel</a>
          <button class="btn btn-primary"><i class="ti ti-send"></i> Post</button>
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
