<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: my_posts.php
 * Tables: posts, post_photos, post_reactions, post_comments
 * Expected Variables:
 *  - $posts => [ {'id','content','created_at','photo_count','reaction_count','comment_count','pet_id','pet_name'}, ... ]
 */
$pageTitle = 'My Posts';
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
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./create_post.php" class="btn btn-sm btn-primary"><i class="ti ti-plus"></i> New Post</a>
      </div>
      <div class="card">
        <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Content</th>
              <th>Pet</th>
              <th>Photos</th>
              <th>Reacts</th>
              <th>Comments</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($posts)): ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No posts yet.</td></tr>
            <?php else: foreach($posts as $po): ?>
              <tr>
                <td class="small" style="max-width:260px;">
                  <a href="./post_view.php?id=<?php echo (int)$po['id']; ?>" class="text-decoration-none">
                    <?php echo htmlspecialchars(mb_strimwidth($po['content'],0,80,'…')); ?>
                  </a>
                </td>
                <td><?php echo !empty($po['pet_id']) ? '<a class="text-decoration-none" href="./pet_view.php?id='.(int)$po['pet_id'].'">'.htmlspecialchars($po['pet_name']).'</a>' : '—'; ?></td>
                <td><?php echo (int)$po['photo_count']; ?></td>
                <td><?php echo (int)$po['reaction_count']; ?></td>
                <td><?php echo (int)$po['comment_count']; ?></td>
                <td><span class="small text-muted"><?php echo htmlspecialchars(date('M d', strtotime($po['created_at']))); ?></span></td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="./post_view.php?id=<?php echo (int)$po['id']; ?>" class="btn btn-outline-secondary"><i class="ti ti-eye"></i></a>
                    <a href="./create_post.php?edit=<?php echo (int)$po['id']; ?>" class="btn btn-outline-secondary"><i class="ti ti-edit"></i></a>
                    <form action="../controllers/create-post-controller.php" method="post" onsubmit="return confirm('Delete this post?');">
                      <input type="hidden" name="post_id" value="<?php echo (int)$po['id']; ?>">
                      <input type="hidden" name="action" value="delete">
                      <button class="btn btn-outline-danger"><i class="ti ti-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./create_post.php" class="btn btn-sm btn-light border">Create Post</a>
            <a href="./community.php" class="btn btn-sm btn-light border">Community</a>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Tips</h6>
          <p class="small text-muted mb-0">Short, engaging content with a photo gets more reactions.</p>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
