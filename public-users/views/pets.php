<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Browse Pets';
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
  </div>
  <div class="card"><div class="card-body"><!-- Placeholder: pet filters and grid/list --></div></div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
