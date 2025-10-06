<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Community Feed';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
// Removed shelter button
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Navigate</h6>
          <div class="d-grid gap-2">
            <a href="./create_post.php" class="btn btn-sm btn-outline-primary">Create Post</a>
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Browse Pets</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./create_post.php" class="btn btn-sm btn-primary"><i class="ti ti-plus"></i> Post</a>
      </div>
      <div class="card mb-3"><div class="card-body"><!-- Placeholder: posts feed and interactions --><p class="text-muted small mb-0">Feed coming soon...</p></div></div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Trending</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="#" class="btn btn-sm btn-light border">#adoptDontShop</a>
            <a href="#" class="btn btn-sm btn-light border">#rescue</a>
            <a href="#" class="btn btn-sm btn-light border">#pets</a>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./create_post.php" class="btn btn-sm btn-light border">Create Post</a>
            <a href="./my_posts.php" class="btn btn-sm btn-light border">My Posts</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
