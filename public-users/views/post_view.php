<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Post';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']); // retained if needed elsewhere
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    
  </div>
  <div class="card"><div class="card-body"><!-- Placeholder: post content with comments --></div></div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
