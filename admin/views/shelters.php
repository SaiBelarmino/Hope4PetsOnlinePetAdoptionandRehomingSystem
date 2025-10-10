<?php
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid">
	<div class="d-flex align-items-center justify-content-between mb-3">
		<h3 class="mb-0">All Shelters</h3>
		<a href="./shelters-create.php" class="btn btn-sm btn-success">Add Shelter</a>
	</div>

	<?php
	// Expected: $shelters = [ ['id'=>1,'name'=>'Happy Paws','address'=>'123 Street','contact'=>'09171234567','logo'=>'../../assets/images/shelter-logo.png'], ... ]
	if (!empty($shelters) && is_array($shelters)) : ?>
		<div class="row">
			<?php foreach ($shelters as $s) :
				$logo = !empty($s['logo']) ? $s['logo'] : '../../assets/images/no-image.png';
				$name = isset($s['name']) ? $s['name'] : 'Unnamed Shelter';
				$address = isset($s['address']) ? $s['address'] : '-';
				$contact = isset($s['contact']) ? $s['contact'] : '-';
			?>
				<div class="col-12 col-md-6 col-lg-4 mb-3">
					<div class="card h-100">
						<div class="card-body d-flex">
							<img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($name); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px;margin-right:12px;" />
							<div>
								<h5 class="mb-1"><?= htmlspecialchars($name); ?></h5>
								<small class="text-muted d-block"><?= htmlspecialchars($address); ?></small>
								<small class="text-muted">Contact: <?= htmlspecialchars($contact); ?></small>
							</div>
						</div>
						<div class="card-footer text-end">
							<a href="./shelter-pets.php?shelter_id=<?= isset($s['id']) ? (int)$s['id'] : 0; ?>" class="btn btn-sm btn-outline-primary">View Pets</a>
							<a href="./shelters-view.php?id=<?= isset($s['id']) ? (int)$s['id'] : 0; ?>" class="btn btn-sm btn-outline-secondary">Profile</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<!-- Fallback sample content -->
		<div class="row">
			<div class="col-12 col-md-6 col-lg-4 mb-3">
				<div class="card h-100">
					<div class="card-body d-flex">
						<img src="../../assets/images/sample-shelter.png" alt="Happy Paws" style="width:80px;height:80px;object-fit:cover;border-radius:6px;margin-right:12px;" />
						<div>
							<h5 class="mb-1">Happy Paws</h5>
							<small class="text-muted d-block">123 Paw Street, City</small>
							<small class="text-muted">Contact: 0917-123-4567</small>
						</div>
					</div>
					<div class="card-footer text-end">
						<a href="./shelter-pets.php?shelter_id=1" class="btn btn-sm btn-outline-primary">View Pets</a>
						<a href="./shelters-view.php?id=1" class="btn btn-sm btn-outline-secondary">Profile</a>
					</div>
				</div>
			</div>
			<div class="col-12 col-md-6 col-lg-4 mb-3">
				<div class="card h-100">
					<div class="card-body d-flex">
						<img src="../../assets/images/sample-shelter.png" alt="Hope Rescue" style="width:80px;height:80px;object-fit:cover;border-radius:6px;margin-right:12px;" />
						<div>
							<h5 class="mb-1">Hope Rescue</h5>
							<small class="text-muted d-block">78 Rescue Avenue, City</small>
							<small class="text-muted">Contact: 0998-765-4321</small>
						</div>
					</div>
					<div class="card-footer text-end">
						<a href="./shelter-pets.php?shelter_id=2" class="btn btn-sm btn-outline-primary">View Pets</a>
						<a href="./shelters-view.php?id=2" class="btn btn-sm btn-outline-secondary">Profile</a>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</div>
