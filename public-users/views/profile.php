<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Profile';
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
  </div>
  <div class="card"><div class="card-body"><!-- Placeholder: profile details --></div></div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
