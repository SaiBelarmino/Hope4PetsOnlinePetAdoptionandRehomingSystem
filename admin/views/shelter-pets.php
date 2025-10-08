<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid">
	<div class="d-flex align-items-center justify-content-between mb-3">
		<h3 class="mb-0">Pets under Shelter</h3>
		<small class="text-muted">View pets grouped by shelter</small>
	</div>

	<?php
	// Expected shape (from controller):
	// $shelterPets = [
	//   [ 'shelter_name' => 'Shelter A', 'pets' => [ ['id'=>1,'name'=>'Max','type'=>'Dog','age'=>'2 yrs','photo'=>'../../assets/images/sample-pet.png'], ... ] ],
	//   ...
	// ];

	if (!empty($shelterPets) && is_array($shelterPets)) : ?>
		<?php foreach ($shelterPets as $group) :
			$shelterName = isset($group['shelter_name']) ? $group['shelter_name'] : 'Shelter';
			$pets = isset($group['pets']) && is_array($group['pets']) ? $group['pets'] : [];
		?>
			<div class="card mb-4">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<h5 class="card-title mb-0"><?= htmlspecialchars($shelterName); ?></h5>
						<span class="text-muted"><?= count($pets); ?> pet(s)</span>
					</div>

					<?php if (count($pets) === 0) : ?>
						<div class="alert alert-info mb-0">No pets registered for this shelter.</div>
					<?php else : ?>
						<div class="row">
							<?php foreach ($pets as $pet) :
								$photo = !empty($pet['photo']) ? $pet['photo'] : '../../assets/images/no-image.png';
								$petName = isset($pet['name']) ? $pet['name'] : 'Unnamed';
								$petType = isset($pet['type']) ? $pet['type'] : '-';
								$petAge = isset($pet['age']) ? $pet['age'] : '-';
							?>
								<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
									<div class="card h-100">
										<img src="<?= htmlspecialchars($photo); ?>" class="card-img-top" alt="<?= htmlspecialchars($petName); ?>" style="height:160px;object-fit:cover;" />
										<div class="card-body p-2">
											<h6 class="mb-1"><?= htmlspecialchars($petName); ?></h6>
											<small class="text-muted d-block">Type: <?= htmlspecialchars($petType); ?></small>
											<small class="text-muted">Age: <?= htmlspecialchars($petAge); ?></small>
										</div>
										<div class="card-footer p-2 text-end">
											<a href="./pets.php?pet_id=<?= isset($pet['id']) ? (int)$pet['id'] : 0; ?>" class="btn btn-sm btn-primary">View</a>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<!-- Fallback sample content so the page isn't empty while controller is not wired -->
		<div class="row">
			<div class="col-12 mb-4">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Example Shelter: Happy Paws</h5>
						<p class="text-muted">Showing a few sample pets registered under this shelter.</p>
						<div class="row">
							<div class="col-6 col-lg-4 mb-3">
								<div class="card h-100">
									<img src="../../assets/images/sample-pet.png" class="card-img-top" alt="Bella" style="height:120px;object-fit:cover;" />
									<div class="card-body p-2">
										<h6 class="mb-1">Bella</h6>
										<small class="text-muted d-block">Dog • 3 yrs</small>
									</div>
								</div>
							</div>
							<div class="col-6 col-lg-4 mb-3">
								<div class="card h-100">
									<img src="../../assets/images/sample-pet.png" class="card-img-top" alt="Milo" style="height:120px;object-fit:cover;" />
									<div class="card-body p-2">
										<h6 class="mb-1">Milo</h6>
										<small class="text-muted d-block">Cat • 1 yr</small>
									</div>
								</div>
							</div>
							<div class="col-6 col-lg-4 mb-3">
								<div class="card h-100">
									<img src="../../assets/images/sample-pet.png" class="card-img-top" alt="Luna" style="height:120px;object-fit:cover;" />
									<div class="card-body p-2">
										<h6 class="mb-1">Luna</h6>
										<small class="text-muted d-block">Dog • 2 yrs</small>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</div>
