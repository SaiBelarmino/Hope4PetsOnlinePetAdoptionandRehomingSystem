<?php
$pageTitle = 'My Adoptions';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../controllers/AdoptionManagementController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

$session = new SessionManager();
$userId = $session->get('user_id');

// Default: show only 'approved' and 'completed' adoptions as "successful".
$adoptions = [];
if ($userId) {
	$adoptions = AdoptController::getMyAdoptions($userId);
}
?>

<div class="container-fluid">
	<div class="row g-3 py-4">
		<!-- Left Sidebar -->
		<?php include __DIR__ . '/../include/shortcut-button.php'; ?>

		<!-- Center Content -->
		<div class="col-12 col-lg-6" style="max-height:862px; overflow:auto; -webkit-overflow-scrolling:touch;">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
			</div>

			<?php if (!$userId): ?>
			<div class="card">
				<div class="card-body text-center text-muted py-5">You must be logged in to view your adoptions.</div>
			</div>
			<?php elseif (empty($adoptions)): ?>
			<div class="card">
				<div class="card-body text-center text-muted py-5">You have not adopted any animals yet.</div>
			</div>
			<?php else: ?>
				<?php foreach ($adoptions as $a): ?>
				<div class="card mb-3">
					<div class="card-body">
						<div class="d-flex align-items-start gap-3">
							<?php
								$ownerId = $a['owner_id'] ?? '';
								$petPhotos = $a['pet_photo'] ?? '';

								if (empty($petPhotos)) {
									$relPath = '/storage/uploads/images/default.png';
								} elseif (strpos($petPhotos, '/storage/uploads') === 0 || preg_match('#^https?://#i', $petPhotos)) {
									$relPath = $petPhotos;
								} else {
									$relPath = '/storage/uploads/images/' . $ownerId . '/' . $petPhotos;
								}

								$candidates = [];
								$candidates[] = $relPath;
								$projectPrefix = '/Hope4PetsOnlinePetAdoptionandRehomingSystem';
								$candidates[] = $projectPrefix . $relPath;

								$petImgPath = '/storage/uploads/images/default.png';
								foreach ($candidates as $url) {
									$fs1 = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $url;
									$fs2 = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $projectPrefix . $url;
									if (file_exists($fs1)) {
										$petImgPath = $url;
										break;
									}
									if (file_exists($fs2)) {
										$petImgPath = $projectPrefix . $url;
										break;
									}
								}
							?>
							<img src="<?php echo htmlspecialchars($petImgPath); ?>" alt="pet" class="rounded-circle"
								style="width:96px; height:96px; object-fit:cover;">

							<div class="flex-grow-1">
								<div class="d-flex justify-content-between">
									<div>
										<h5 class="mb-1"><?php echo htmlspecialchars($a['pet_name'] ?? ('Pet ID: ' . ($a['pet_id'] ?? ''))); ?></h5>
										<div class="small text-muted">Adopted on <?php echo htmlspecialchars(date('M d, Y', strtotime($a['created_at'] ?? ''))); ?></div>
									</div>
									<div class="text-end">
										<span class="badge bg-<?php echo ($a['status']==='approved')? 'warning' : 'success'; ?>"><?php echo htmlspecialchars(ucfirst($a['status'] ?? '')); ?></span>
									</div>
								</div>

								<hr class="my-2">

								<div class="d-flex gap-2">
									<a href="./PetView.php?pet_id=<?php echo (int)$a['pet_id']; ?>" class="btn btn-sm btn-outline-primary">View Pet</a>
									<a href="./ChatMessages.php?user_id=<?php echo (int)$a['owner_id']; ?>" class="btn btn-sm btn-outline-secondary">Message Shelter</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>

		</div>

		<!-- Right Sidebar -->
		<div class="col-12 col-lg-2">
			<div class="card mb-3">
				<div class="card-header bg-white border-0 pb-0">
					<h6 class="mb-0">Summary</h6>
				</div>
				<div class="card-body small">
					<p class="mb-1"><strong>Total successful adoptions:</strong> <?php echo is_array($adoptions) ? count($adoptions) : 0; ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
