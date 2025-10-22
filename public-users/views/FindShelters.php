<?php 
// Set page title for header include
$pageTitle = 'Find Shelters';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';
?>
<div class="container-fluid">
  <div class="row g-3 py-4">
	<?php include __DIR__ . '/../include/shortcut-button.php'; ?>
	<div class="col-12 col-lg-8">
	  <div class="card mb-3">
		<div class="card-body">
		  <h2 class="h4 mb-3"><i class="ti ti-building-community text-info me-2"></i>Find Animal Shelters</h2>
		  <div class="row mb-3">
			<div class="col-md-8">
			  <input type="text" id="shelterSearch" class="form-control" placeholder="Search shelters by name or city...">
			</div>
		  </div>
		  <div id="shelterList" class="row g-3"></div>
		  <div id="noResults" class="alert alert-info d-none">No shelters found.</div>
		</div>
	  </div>
	</div>
  </div>
</div>

<!-- Shelter Details Modal -->
<div class="modal fade" id="shelterModal" tabindex="-1" aria-labelledby="shelterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="shelterModalLabel">Shelter Details</h5>
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body" id="shelterModalBody">
		<div class="text-center">Loading...</div>
	  </div>
	</div>
  </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
<script src="assets/js/findshelter.js"></script>
