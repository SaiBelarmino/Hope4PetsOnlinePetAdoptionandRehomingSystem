<?php
$pageTitle = 'My Adoptions';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';

require_once __DIR__ . '/../controllers/AdoptionManagementController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

$session = new SessionManager();
$userId = $session->get('user_id');

// Attempt to fetch all adoptions for the user. Try multiple controller method names if available.
$adoptions = [];
if ($userId) {
    if (class_exists('AdoptController')) {
        if (method_exists('AdoptController', 'getMyAdoptionsAll')) {
            $adoptions = AdoptController::getMyAdoptionsAll($userId);
        } elseif (method_exists('AdoptController', 'getAllUserAdoptions')) {
            $adoptions = AdoptController::getAllUserAdoptions($userId);
        } elseif (method_exists('AdoptController', 'getMyAdoptions')) {
            $adoptions = AdoptController::getMyAdoptions($userId);
        }
    } else {
        // Fallback: try calling the common method but guard with function_exists/class check above.
        if (function_exists('AdoptController')) {
            $adoptions = AdoptController::getMyAdoptions($userId);
        }
    }
}

// Optional server-side filtering by status (via ?filter_status=pending|approved|rejected|cancelled|all)
$filterStatus = isset($_GET['filter_status']) ? strtolower(trim($_GET['filter_status'])) : 'all';
if ($filterStatus !== 'all' && is_array($adoptions)) {
    $adoptions = array_values(array_filter($adoptions, function ($a) use ($filterStatus) {
        $s = strtolower($a['status'] ?? '');
        if ($filterStatus === 'cancelled' || $filterStatus === 'cancel') {
            return in_array($s, ['cancel', 'cancelled', 'canceled'], true);
        }
        if ($filterStatus === 'rejected' || $filterStatus === 'reject') {
            return in_array($s, ['rejected', 'reject'], true);
        }
        return $s === $filterStatus;
    }));
}

// Compute summary counts
$counts = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0, 'other' => 0];
if (is_array($adoptions)) {
    foreach ($adoptions as $a) {
        $s = strtolower($a['status'] ?? '');
        if ($s === 'approved') {
            $counts['approved']++;
        } elseif (in_array($s, ['rejected', 'reject'], true)) {
            $counts['rejected']++;
        } elseif (in_array($s, ['cancel', 'cancelled', 'canceled'], true)) {
            $counts['cancelled']++;
        } elseif ($s === 'pending') {
            $counts['pending']++;
        } else {
            $counts['other']++;
        }
        $counts['total']++;
    }
}
?>

<div class="container-fluid">
	<div class="row g-3 py-4">
		<!-- Left Sidebar -->
		<?php include __DIR__ . '/../include/shortcut-button.php'; ?>

		<!-- Center Content -->
		<div class="col-12 col-lg-6" style="max-height:862px; overflow:auto; -webkit-overflow-scrolling:touch;">
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
										<?php
                                            $statusRaw = $a['status'] ?? '';
                                            $status = strtolower($statusRaw);
                                            if ($status === 'approved') {
                                                $badgeClass = 'success';
                                            } elseif ($status === 'rejected' || $status === 'reject') {
                                                $badgeClass = 'danger';
                                            } elseif (in_array($status, ['cancel', 'cancelled', 'canceled'], true)) {
                                                $badgeClass = 'warning';
                                            } else {
                                                $badgeClass = 'secondary';
                                            }
                                        ?>
                                        <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($statusRaw)); ?></span>
									</div>
								</div>

								<hr class="my-2">

								<div class="d-flex gap-2">
									<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal-<?php echo (int)$a['pet_id']; ?>">View Pet</button>
									<a href="./ChatMessages.php?user_id=<?php echo (int)$a['owner_id']; ?>" class="btn btn-sm btn-outline-secondary">Message Shelter</a>
								</div>
								<!-- View Pet Modal (matches BrowsePet.php style) -->
								<div class="modal fade" id="viewModal-<?php echo (int)$a['pet_id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel-<?php echo (int)$a['pet_id']; ?>" aria-hidden="true">
									<div class="modal-dialog modal-lg modal-dialog-centered">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="viewModalLabel-<?php echo (int)$a['pet_id']; ?>">
													<?php echo htmlspecialchars($a['pet_name'] ?? 'Pet Profile'); ?></h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<div class="row g-3">
													<div class="col-12 col-md-5">
														<img src="<?php echo htmlspecialchars($petImgPath); ?>" class="img-fluid rounded w-100 object-fit-cover" alt="<?php echo htmlspecialchars($a['pet_name'] ?? 'Pet'); ?>">
													</div>
													<div class="col-12 col-md-7">
														<div class="mb-2"><strong>Breed:</strong> <?php echo htmlspecialchars($a['pet_breed'] ?? 'Unknown'); ?></div>
														<div class="mb-2"><strong>Age:</strong> <?php echo htmlspecialchars($a['pet_age'] ?? ''); ?></div>
														<div class="mb-2"><strong>Species:</strong> <?php echo htmlspecialchars($a['pet_species'] ?? 'Other'); ?></div>
														<div class="mb-2"><strong>Gender:</strong> <?php echo htmlspecialchars($a['pet_gender'] ?? 'Unknown'); ?></div>
														<div class="mb-2"><strong>Size:</strong> <?php echo htmlspecialchars($a['pet_size'] ?? 'Medium'); ?></div>
														<div class="mb-2"><strong>Vaccine:</strong> <?php echo htmlspecialchars($a['pet_vaccine_status'] ?? 'N/A'); ?></div>
														<div class="mb-2"><strong>Health:</strong> <?php echo htmlspecialchars($a['pet_health_status'] ?? 'N/A'); ?></div>
														<div class="mb-2"><strong>Owner:</strong> <?php echo htmlspecialchars($a['owner_name'] ?? ''); ?></div>
														<?php if (!empty($a['shelter_name']) || !empty($a['pet_location']) || !empty($a['shelter_address'])): ?>
														<hr>
														<h6>Shelter / Location</h6>
														<?php if (!empty($a['shelter_name'])): ?><div class="mb-2"><strong>Shelter:</strong> <?php echo htmlspecialchars($a['shelter_name']); ?></div><?php endif; ?>
														<?php if (!empty($a['shelter_address'])): ?><div class="mb-2"><strong>Address:</strong> <?php echo htmlspecialchars($a['shelter_address']); ?></div><?php endif; ?>
														<?php if (!empty($a['pet_location'])): ?>
														<div class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($a['pet_location']); ?></div>
														<div class="mb-2"><a href="https://www.google.com/maps/search/<?php echo urlencode($a['pet_location']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">View on Google Maps</a></div>
														<?php endif; ?>
														<?php endif; ?>
													</div>
													<div class="col-12">
														<hr>
														<h6>Description</h6>
														<div class="bg-light rounded p-2" style="min-height:60px;white-space:pre-line;">
															<?php echo nl2br(htmlspecialchars($a['pet_description'] ?? 'No description')); ?>
														</div>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- Pet Details Modal -->
							<div class="modal fade" id="petDetailsModal" tabindex="-1" aria-labelledby="petDetailsModalLabel" aria-hidden="true">
								<div class="modal-dialog modal-lg modal-dialog-centered">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title" id="petDetailsModalLabel">Pet Details</h5>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<div class="modal-body">
											<div class="row g-3 align-items-center">
												<div class="col-md-4 text-center">
													<img id="modalPetPhoto" src="" alt="Pet Photo" class="img-fluid rounded shadow" style="max-height:220px;object-fit:cover;">
												</div>
												<div class="col-md-8">
													<h3 id="modalPetName" class="mb-2 text-primary"></h3>
													<div class="mb-2">
														<span class="badge bg-info text-dark me-1" id="modalPetBreed"></span>
														<span class="badge bg-secondary me-1" id="modalPetSpecies"></span>
														<span class="badge bg-success" id="modalPetAge"></span>
													</div>
													<p id="modalPetDescription" class="mt-3 mb-2" style="min-height:60px;"></p>
													<div class="d-flex flex-wrap gap-2 align-items-center mb-2">
														<span class="fw-bold">Status:</span>
														<span class="badge" id="modalPetStatus"></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<script>
							document.addEventListener('DOMContentLoaded', function() {
								var petModal = new bootstrap.Modal(document.getElementById('petDetailsModal'));
								document.querySelectorAll('.view-pet-btn').forEach(function(btn) {
									btn.addEventListener('click', function() {
										var pet = JSON.parse(this.getAttribute('data-pet'));
										document.getElementById('modalPetPhoto').src = pet.photo || '../../assets/images/placeholder.png';
										document.getElementById('modalPetName').textContent = pet.name || '';
										document.getElementById('modalPetBreed').textContent = pet.breed ? 'Breed: ' + pet.breed : '';
										document.getElementById('modalPetSpecies').textContent = pet.species ? 'Species: ' + pet.species : '';
										document.getElementById('modalPetAge').textContent = pet.age ? 'Age: ' + pet.age : '';
										document.getElementById('modalPetDescription').textContent = pet.description || 'No description available.';
										// Status badge
										var status = (pet.status || '').toLowerCase();
										var badge = 'bg-secondary';
										if (status === 'available') badge = 'bg-success';
										else if (status === 'adopted') badge = 'bg-primary';
										else if (status === 'pending') badge = 'bg-warning';
										else if (status === 'rehomed') badge = 'bg-info';
										var statusElem = document.getElementById('modalPetStatus');
										statusElem.className = 'badge ' + badge + ' text-uppercase px-3 py-2';
										statusElem.textContent = status.charAt(0).toUpperCase() + status.slice(1);
										petModal.show();
									});
								});
							});
							</script>
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
