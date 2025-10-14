</div>
</div>
</div> <!-- /.body-wrapper -->
</div> <!-- /.page-wrapper -->
<script src="../../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="../../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
<script src="../../assets/libs/simplebar/dist/simplebar.min.js"></script>
<script src="../../assets/js/sidebarmenu.js"></script>
<script src="../../assets/js/app.min.js"></script>
<script src="../../assets/js/dashboard.js"></script>
<script src="../../assets/js/sweetalert2@11.js"></script>
<!-- Create Post Modal injected here so no extra file is required -->
<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Create Post</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="modal_create_post_form" method="post" action="../controllers/CreatePostController.php" enctype="multipart/form-data">
					<input type="hidden" name="action" value="create" />
					<div class="mb-3">
						<label for="modal_content" class="form-label">What's on your mind?</label>
						<textarea id="modal_content" name="content" class="form-control" rows="4" placeholder="Put something here, or leave empty and attach images/videos."></textarea>
					</div>

					<div class="mb-3">
						<label class="form-label">Photos (you can add multiple)</label>
						<div id="modal_photos_inputs">
							<input type="file" name="photos[]" id="modal_photos" accept="image/*" multiple class="form-control mb-2" />
						</div>
						<div class="d-flex gap-2 mb-2">
							<button type="button" id="modal_add_photo_input" class="btn btn-sm btn-outline-primary">Add another image</button>
							<small id="modal_photos_count" class="text-muted">0 selected</small>
						</div>
						<div id="modal_photos_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
					</div>

					<div class="mb-3">
						<label class="form-label">Video (one)</label>
						<input type="file" name="video" id="modal_video" accept="video/*" class="form-control" />
						<div id="modal_video_preview" class="mt-2"></div>
					</div>
					<small class="text-muted">You can add multiple images. Max recommended: 8 images. One video per post.</small>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" form="modal_create_post_form" class="btn btn-primary">Post</button>
			</div>
		</div>
	</div>
</div>

<script>
// Scoped client-side previews for the modal form. Uses modal-local querySelectors to avoid clashing with the standalone page form.
document.addEventListener('DOMContentLoaded', function(){
	const modalEl = document.getElementById('createPostModal');
	if (!modalEl) return;

	const MAX_IMAGES = 8;
	const photosInput = modalEl.querySelector('#modal_photos');
	const photosInputsWrap = modalEl.querySelector('#modal_photos_inputs');
	const addPhotoBtn = modalEl.querySelector('#modal_add_photo_input');
	const photosPreview = modalEl.querySelector('#modal_photos_preview');
	const photosCountLabel = modalEl.querySelector('#modal_photos_count');
	const videoInput = modalEl.querySelector('#modal_video');
	const videoPreview = modalEl.querySelector('#modal_video_preview');

	const previewKeys = new Set();

	function updatePhotosCount(){
		const count = photosPreview.querySelectorAll('img').length;
		photosCountLabel.textContent = count + ' selected';
	}

	function addImageElement(src, key){
		if (photosPreview.querySelectorAll('img').length >= MAX_IMAGES) return false;
		if (key && previewKeys.has(key)) return false;
		const img = document.createElement('img');
		img.src = src;
		img.style.width = '120px'; img.style.height = '120px'; img.style.objectFit = 'cover'; img.className='rounded';
		photosPreview.appendChild(img);
		if (key) previewKeys.add(key);
		updatePhotosCount();
		return true;
	}

	function addFilesToPreview(files){
		const existingCount = photosPreview.querySelectorAll('img').length;
		const available = Math.max(0, MAX_IMAGES - existingCount);
		const slice = Array.from(files).slice(0, available);
		slice.forEach(file => {
			if (!file.type.startsWith('image/')) return;
			const key = file.name + '_' + file.size;
			if (previewKeys.has(key)) return;
			const reader = new FileReader();
			reader.onload = function(ev){ addImageElement(ev.target.result, key); };
			reader.readAsDataURL(file);
		});
	}

	function handleFileInputChange(ev){
		const files = ev.target.files || [];
		addFilesToPreview(files);
	}

	if (photosInput) photosInput.addEventListener('change', handleFileInputChange);

	if (addPhotoBtn){
		addPhotoBtn.addEventListener('click', function(){
			const currentInputs = photosInputsWrap.querySelectorAll('input[type=file]');
			if (currentInputs.length >= 4) return;
			const inp = document.createElement('input');
			inp.type = 'file'; inp.name = 'photos[]'; inp.accept = 'image/*'; inp.className = 'form-control mb-2';
			inp.addEventListener('change', handleFileInputChange);
			photosInputsWrap.appendChild(inp);
		});
	}

	if (videoInput){
		videoInput.addEventListener('change', function(){
			videoPreview.innerHTML = '';
			const file = this.files[0];
			if (!file) return;
			if (!file.type.startsWith('video/')) return;
			const url = URL.createObjectURL(file);
			const video = document.createElement('video');
			video.src = url; video.controls = true; video.style.maxWidth='100%'; video.style.maxHeight='320px';
			videoPreview.appendChild(video);
		});
	}

	// Reset modal form when hidden to ensure fresh state
	modalEl.addEventListener('hidden.bs.modal', function(){
		const form = modalEl.querySelector('#modal_create_post_form');
		if (form) form.reset();
		photosPreview.innerHTML = '';
		previewKeys.clear();
		updatePhotosCount();
		if (videoPreview) videoPreview.innerHTML = '';
	});

	// Initialize photo count
	updatePhotosCount();
});
</script>

</body>

</html>
<?php /* preloader removed for public-users */ ?>