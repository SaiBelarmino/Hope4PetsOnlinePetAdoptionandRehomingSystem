<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'My Shelter Profile';
$hasShelter = true; // On this page we assume user already has shelter (button removed)
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    <a href="./edit_shelter.php" class="btn btn-secondary"><i class="ti ti-edit me-1"></i>Edit Shelter</a>
  </div>
  <div class="card"><div class="card-body"><!-- Placeholder: shelter overview, stats, manage pets link --></div></div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
