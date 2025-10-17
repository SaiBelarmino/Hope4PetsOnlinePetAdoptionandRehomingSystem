<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../controllers/PetController.php';

$pageTitle = 'Browse Pets';

$pets = PetController::fetchAvailablePets(36, 0);

?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<div class="container-fluid">
	<?php
	// show flash if any
	if (session_status() === PHP_SESSION_NONE) { session_start(); }
	$flash = $_SESSION['flash'] ?? null;
	if ($flash) {
			$type = ($flash['type'] ?? 'info') === 'success' ? 'success' : 'danger';
			echo '<div class="container mt-3"><div class="alert alert-' . $type . '">' . htmlspecialchars($flash['message'] ?? '') . '</div></div>';
			unset($_SESSION['flash']);
	}
	?>
	<div class="row g-3 py-3">
		<div class="col-12 col-lg-3">
			<?php include __DIR__ . '/../include/shortcut-button.php'; ?>
		</div>
		<div class="col-12 col-lg-6">
			<h3 class="mb-3">Browse Pets</h3>

					<div class="row g-3">
						<?php if (empty($pets)): ?>
							<div class="col-12">
								<div class="card"><div class="card-body text-center text-muted py-5">No pets available right now.</div></div>
							</div>
						<?php else: foreach ($pets as $p):
								$photo = $p['photo'] ?? '/storage/uploads/images/default.png';
								$ownerId = (int)($p['owner_id'] ?? 0);
								$ownerName = htmlspecialchars($p['owner_name'] ?? ($p['full_name'] ?? 'Owner'));
								$shelterName = htmlspecialchars($p['shelter_name'] ?? '');
						?>
						<div class="col-12 col-sm-6 col-md-4">
							<div class="card h-100">
								<div class="ratio ratio-4x3 overflow-hidden">
									<img src="<?php echo htmlspecialchars($photo); ?>" class="card-img-top object-fit-cover" alt="<?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?>">
								</div>
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-start mb-2">
										<div>
											<h6 class="mb-1"><a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($p['name'] ?? 'Unnamed'); ?></a></h6>
											<div class="small text-muted"><?php echo htmlspecialchars($p['breed'] ?? 'Unknown'); ?> · <?php echo htmlspecialchars($p['age'] ?? ''); ?></div>
										</div>
										<div class="text-end">
											<span class="badge bg-<?php echo (($p['status'] ?? '')==='available')? 'success' : ((($p['status'] ?? '')==='adopted')? 'secondary' : 'warning'); ?>"><?php echo htmlspecialchars(ucfirst($p['status'] ?? '')); ?></span>
										</div>
									</div>

									<div class="small mb-2">
										<div><strong>Species:</strong> <?php echo htmlspecialchars($p['species'] ?? 'Other'); ?></div>
										<div><strong>Gender:</strong> <?php echo htmlspecialchars($p['gender'] ?? 'Unknown'); ?></div>
										<div><strong>Size:</strong> <?php echo htmlspecialchars($p['size'] ?? 'Medium'); ?></div>
										<div><strong>Vaccine:</strong> <?php echo htmlspecialchars($p['vaccine_status'] ?? 'N/A'); ?></div>
										<div><strong>Health:</strong> <?php echo htmlspecialchars($p['health_status'] ?? 'N/A'); ?></div>
										<div><strong>Location:</strong> <?php echo htmlspecialchars($p['location'] ?? 'Unknown'); ?></div>
										<?php if (!empty($shelterName)): ?><div><strong>Shelter:</strong> <?php echo $shelterName; ?></div><?php endif; ?>
										<div class="mt-1"><small class="text-muted">Owner: <?php echo $ownerName; ?></small></div>
									</div>

									<p class="small text-truncate mb-2"><?php echo htmlspecialchars($p['description'] ?? 'No description'); ?></p>

									<div class="d-flex gap-2">
										<a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-outline-primary flex-grow-1">View Profile</a>

										<!-- Adopt button: go to adoption process page where user can confirm -->
										<form method="get" action="./adoption_process.php" class="adopt-form" style="margin:0;">
											<input type="hidden" name="pet_id" value="<?php echo (int)$p['id']; ?>">
											<button type="submit" class="btn btn-sm btn-success" <?php echo (($p['status'] ?? '') !== 'available') ? 'disabled' : ''; ?>>Adopt</button>
										</form>

										<!-- Message button: open chat with owner (ChatMessages.php?user_id=OWNER_ID) -->
										<a href="./ChatMessages.php?user_id=<?php echo $ownerId; ?>" class="btn btn-sm btn-outline-secondary"><i class="ti ti-message"></i></a>
									</div>
								</div>
							</div>
						</div>
						<?php endforeach; endif; ?>
			</div>
		</div>
		<div class="col-12 col-lg-3">
			<div class="card">
				<div class="card-body">
					<h6 class="mb-2">Filters</h6>
					<p class="small text-muted">Use search and filters on the left to narrow results.</p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<!-- Reuse Verify ID Modal (copied from MyProfile) -->
<div class="modal fade" id="verifyIdModal" tabindex="-1" aria-labelledby="verifyIdModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="verifyIdModalLabel">Verify ID</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="verifyIdFormInline" method="post" action="../controllers/EditMyProfileController.php" enctype="multipart/form-data">
					<input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user']['id'] ?? ''); ?>">
					<input type="hidden" name="action" value="verify_id">
					<div class="mb-3">
						<label for="docTypeInline" class="form-label">Select Document Type</label>
						<select class="form-select" id="docTypeInline" name="doc_type" required>
							<option value="" selected disabled>Choose...</option>
							<option value="Philippine National ID (PhilSys/ePhilID)">Philippine National ID (PhilSys/ePhilID)</option>
							<option value="Passport">Passport</option>
							<option value="Driver's License">Driver's License</option>
							<option value="UMID Card (Unified Multi-Purpose ID)">UMID Card (Unified Multi-Purpose ID)</option>
							<option value="Professional Regulation Commission (PRC) ID">Professional Regulation Commission (PRC) ID</option>
							<option value="Social Security System (SSS) ID (with date of birth visible)">Social Security System (SSS) ID (with date of birth visible)</option>
							<option value="Voter's ID or Voter's Certification">Voter's ID or Voter's Certification</option>
							<option value="Postal ID">Postal ID</option>
						</select>
					</div>
					<div class="mb-3">
						<button type="button" class="btn btn-primary" id="openCameraBtnInline" disabled>Open Camera & Take Picture</button>
					</div>
					<div id="cameraContainerInline" style="display: none;">
						<p id="captureLabelInline">Capture Front of ID</p>
						<video id="videoInline" width="100%" height="300" autoplay></video>
						<canvas id="canvasInline" style="display: none;"></canvas>
						<div id="capturedImagesInline" style="display: none;">
							<div class="d-flex flex-column align-items-center">
								<div class="text-center mb-3">
									<p>Front</p>
									<img id="frontImgInline" style="width: 200px; height: 120px; object-fit: cover; border: 1px solid #ccc;">
									<br>
									<button class="btn btn-danger btn-sm mt-2" id="removeFrontBtnInline" style="display: none;">Remove</button>
								</div>
								<div class="text-center" id="backContainerInline" style="display: none;">
									<p>Back</p>
									<img id="backImgInline" style="width: 200px; height: 120px; object-fit: cover; border: 1px solid #ccc;">
									<br>
									<button class="btn btn-danger btn-sm mt-2" id="removeBackBtnInline" style="display: none;">Remove</button>
								</div>
								<button class="btn btn-danger btn-sm mt-2" id="removeAllBtnInline" style="display: none;">Remove All Photos</button>
							</div>
						</div>
						<br>
						<button type="button" class="btn btn-success" id="captureBtnInline">Capture</button>
						<button type="button" class="btn btn-primary" id="nextBackBtnInline" style="display: none;">Next Back</button>
						<button type="button" class="btn btn-secondary" id="retakeBtnInline" style="display: none;">Retake</button>
					</div>
					<input type="hidden" name="id_photo" id="idPhotoInputInline">
					<input type="hidden" name="id_photo_back" id="idPhotoBackInputInline">
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" form="verifyIdFormInline" class="btn btn-primary" id="submitVerificationBtnInline" disabled>Submit Verification</button>
			</div>
		</div>
	</div>
</div>

<script>
// expose minimal user info to JS
window.currentUser = <?php echo json_encode($_SESSION['user'] ?? null); ?>;

document.querySelectorAll('.adopt-form').forEach(function(form){
	form.addEventListener('submit', async function(e){
		// if user not logged in, allow navigation to adoption_process which will redirect to login
		if (!window.currentUser || !window.currentUser.id) return true;

		// Always re-check verification with server (in case admin approved recently)
		e.preventDefault();
		try {
			const res = await fetch('/Hope4PetsOnlinePetAdoptionandRehomingSystem/public-users/controllers/check_verification.php', {cache: 'no-store'});
			const data = await res.json();
			if (!data || !data.ok) {
				// not logged in? allow navigation so server handler will redirect to login
				if (data && data.message === 'not_logged_in') {
					form.submit();
					return true;
				}
				// show verify modal as fallback
				var modal = new bootstrap.Modal(document.getElementById('verifyIdModal'));
				modal.show();
				return false;
			}
			if (data.is_verified) {
				// proceed to adoption form
				form.submit();
				return true;
			} else {
				// show verify modal
				var modal = new bootstrap.Modal(document.getElementById('verifyIdModal'));
				modal.show();
				return false;
			}
		} catch (err) {
			console.error('verification check failed', err);
			// fallback to UI: if currentUser.is_verified true then allow, else show modal
			if (window.currentUser && window.currentUser.is_verified) {
				form.submit();
			} else {
				var modal = new bootstrap.Modal(document.getElementById('verifyIdModal'));
				modal.show();
			}
			return false;
		}
	});
});

// Minimal camera/capture wiring for inline modal (lightweight variant of myprofile.js)
(function(){
	// Elements
	var openBtn = document.getElementById('openCameraBtnInline');
	var video = document.getElementById('videoInline');
	var canvas = document.getElementById('canvasInline');
	var idInput = document.getElementById('idPhotoInputInline');
	var idBackInput = document.getElementById('idPhotoBackInputInline');
	var captureBtn = document.getElementById('captureBtnInline');
	var nextBack = document.getElementById('nextBackBtnInline');
	var retakeBtn = document.getElementById('retakeBtnInline');
	var capturedImages = document.getElementById('capturedImagesInline');
	var frontImg = document.getElementById('frontImgInline');
	var backImg = document.getElementById('backImgInline');
	var backContainer = document.getElementById('backContainerInline');
	var removeFront = document.getElementById('removeFrontBtnInline');
	var removeBack = document.getElementById('removeBackBtnInline');
	var removeAll = document.getElementById('removeAllBtnInline');
	var submitBtn = document.getElementById('submitVerificationBtnInline');
	var docType = document.getElementById('docTypeInline');

	if (!openBtn || !video || !canvas || !captureBtn) return;

	var isFront = true;
	var stream = null;

	function startCamera(){
		return navigator.mediaDevices.getUserMedia({video:true}).then(function(s){
			stream = s; video.srcObject = s; video.play(); video.style.display = 'block';
			return true;
		}).catch(function(err){
			console.error('Camera error', err); return false;
		});
	}
	function stopCamera(){ if (stream){ stream.getTracks().forEach(function(t){ t.stop(); }); stream = null; } video.srcObject = null; video.style.display = 'none'; }

	// enable open camera when doc type selected
	if (docType) {
		docType.addEventListener('change', function(){ openBtn.disabled = !this.value; });
	}

	openBtn.addEventListener('click', function(){
		openBtn.disabled = true;
		document.getElementById('cameraContainerInline').style.display = 'block';
		isFront = true;
		captureBtn.style.display = 'inline-block';
		nextBack.style.display = 'none';
		retakeBtn.style.display = 'none';
		backContainer.style.display = 'none';
		capturedImages.style.display = 'none';
		startCamera();
	});

	captureBtn.addEventListener('click', function(){
		if (!video || video.readyState < 2) return; // not ready
		canvas.width = video.videoWidth; canvas.height = video.videoHeight;
		canvas.getContext('2d').drawImage(video,0,0);
		var data = canvas.toDataURL('image/png');

		if (isFront) {
			// Save front
			idInput.value = data;
			frontImg.src = data;
			capturedImages.style.display = 'block';
			document.getElementById('captureLabelInline').textContent = 'ID Photos Captured';
			stopCamera();
			isFront = false;
			captureBtn.style.display = 'none';
			nextBack.style.display = 'inline-block';
			retakeBtn.style.display = 'inline-block';
			removeFront.style.display = 'inline-block';
		} else {
			// Save back
			idBackInput.value = data;
			backImg.src = data;
			backContainer.style.display = 'block';
			capturedImages.style.display = 'block';
			stopCamera();
			captureBtn.style.display = 'none';
			nextBack.style.display = 'none';
			retakeBtn.style.display = 'inline-block';
			removeBack.style.display = 'inline-block';
			submitBtn.disabled = false;
		}
	});

	// Next Back: open camera for back capture
	if (nextBack) nextBack.addEventListener('click', function(){
		nextBack.style.display = 'none';
		captureBtn.style.display = 'inline-block';
		retakeBtn.style.display = 'none';
		startCamera().then(function(){ isFront = false; });
	});

	// Retake: retake front or back
	if (retakeBtn) retakeBtn.addEventListener('click', function(){
		if (backContainer && backContainer.style.display === 'block') {
			// retake back
			backContainer.style.display = 'none'; idBackInput.value = ''; backImg.src = '';
			submitBtn.disabled = true;
			startCamera().then(function(){ isFront = false; captureBtn.style.display='inline-block'; nextBack.style.display='none'; retakeBtn.style.display='none'; });
		} else {
			// retake front
			capturedImages.style.display = 'none'; frontImg.src = ''; idInput.value = ''; removeFront.style.display='none';
			submitBtn.disabled = true;
			startCamera().then(function(){ isFront = true; captureBtn.style.display='inline-block'; nextBack.style.display='none'; retakeBtn.style.display='none'; });
		}
	});

	if (removeFront) removeFront.addEventListener('click', function(){ frontImg.src=''; idInput.value=''; capturedImages.style.display='none'; removeFront.style.display='none'; submitBtn.disabled=true; });
	if (removeBack) removeBack.addEventListener('click', function(){ backImg.src=''; idBackInput.value=''; backContainer.style.display='none'; removeBack.style.display='none'; submitBtn.disabled=true; });
	if (removeAll) removeAll.addEventListener('click', function(){ frontImg.src=''; backImg.src=''; idInput.value=''; idBackInput.value=''; capturedImages.style.display='none'; backContainer.style.display='none'; removeFront.style.display='none'; removeBack.style.display='none'; submitBtn.disabled=true; });

})();
</script>

