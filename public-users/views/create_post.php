<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/SessionManager.php';
require_once __DIR__ . '/../controllers/CreatePostController.php';

use PublicCreatePostController as CPC;

// If editing, load existing post
$isEdit = false;
$editPost = null;
$userId = $_SESSION['user']['id'] ?? null;
if (!empty($_GET['edit'])) {
	$postId = (int)$_GET['edit'];
	$editPost = CPC::getPostForEdit($postId, $userId);
	if ($editPost) { $isEdit = true; }
}

$flash = \SessionManager::getFlash();
$flashSuccess = null;
$flashError = null;
if ($flash && is_array($flash)) {
	$t = $flash['type'] ?? null;
	$m = $flash['message'] ?? null;
	if ($t === 'success') { $flashSuccess = $m; }
	else { $flashError = $m; }
}
?>

<div class="container mt-4">
	<div class="row justify-content-center">
		<div class="col-12 col-md-8">
			<?php if ($flashSuccess): ?>
				<div class="alert alert-success"><?php echo htmlspecialchars((string)$flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
			<?php endif; ?>
			<?php if ($flashError): ?>
				<div class="alert alert-danger"><?php echo htmlspecialchars((string)$flashError, ENT_QUOTES, 'UTF-8'); ?></div>
			<?php endif; ?>

			<div class="card">
				<div class="card-body">
					<h5 class="card-title"><?php echo $isEdit ? 'Edit Post' : 'Create Post'; ?></h5>
					<form method="post" action="../controllers/CreatePostController.php" enctype="multipart/form-data">
						<input type="hidden" name="action" value="<?php echo $isEdit ? 'update' : 'create'; ?>" />
						<?php if ($isEdit): ?>
							<input type="hidden" name="post_id" value="<?php echo (int)$editPost['id']; ?>" />
						<?php endif; ?>

						<div class="mb-3">
							<label for="content" class="form-label">What's on your mind?</label>
							<textarea id="content" name="content" class="form-control" rows="4" placeholder="Put something here, or leave empty and attach images/videos."><?php echo htmlspecialchars($editPost['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
						</div>

						<div class="mb-3">
							<label class="form-label">Photos (you can add multiple)</label>
							<div id="photos-inputs">
								<input type="file" name="photos[]" id="photos" accept="image/*" multiple class="form-control mb-2" />
							</div>
							<div class="d-flex gap-2 mb-2">
								<button type="button" id="add-photo-input" class="btn btn-sm btn-outline-primary">Add another image</button>
								<small id="photos-count" class="text-muted">0 selected</small>
							</div>
							<div id="photos-preview" class="d-flex flex-wrap gap-2 mt-2">
								<?php if ($isEdit && !empty($editPost['photos'])):
									// photos stored as CSV via GROUP_CONCAT in controller (photo_path)
									$existing = explode(',', $editPost['photos']);
									foreach ($existing as $p):
										$p = trim($p);
										if ($p === '') continue;
								?>
									<img src="../../<?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>" class="rounded" style="width:120px;height:120px;object-fit:cover;" alt="Existing photo" />
								<?php endforeach; endif; ?>
							</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Video (one)</label>
							<input type="file" name="video" id="video" accept="video/*" class="form-control" />
							<div id="video-preview" class="mt-2"></div>
						</div>
						<small class="text-muted">You can add multiple images. Max recommended: 8 images. One video per post.</small>

						<div class="d-flex justify-content-between">
							<a href="./index.php" class="btn btn-secondary">Cancel</a>
							<button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update Post' : 'Post'; ?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
// Client-side previews for images and single video
const MAX_IMAGES = 8;
const preview = document.getElementById('photos-preview');
const photosCountLabel = document.getElementById('photos-count');
// Track seen previews to avoid duplicates: for files use name_size, for existing images use src
const previewKeys = new Set();

function addImageElement(src, key) {
    if (preview.querySelectorAll('img').length >= MAX_IMAGES) return false;
    if (key && previewKeys.has(key)) return false;
    const img = document.createElement('img');
    img.src = src;
    img.style.width = '120px'; img.style.height = '120px'; img.style.objectFit = 'cover'; img.className='rounded';
    preview.appendChild(img);
    if (key) previewKeys.add(key);
    updatePhotosCount();
    return true;
}

function addFilesToPreview(files) {
    const existingCount = preview.querySelectorAll('img').length;
    const available = Math.max(0, MAX_IMAGES - existingCount);
    const slice = Array.from(files).slice(0, available);
    slice.forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const key = file.name + '_' + file.size;
        if (previewKeys.has(key)) return; // skip duplicates
        const reader = new FileReader();
        reader.onload = function(ev){
            addImageElement(ev.target.result, key);
        };
        reader.readAsDataURL(file);
    });
}

// initialize previewKeys from any existing images (edit mode)
(function initExistingPreviews(){
    const imgs = preview.querySelectorAll('img');
    imgs.forEach(img => {
        if (img.src) previewKeys.add(img.src);
    });
    updatePhotosCount();
})();

// main handler for file inputs
function handleFileInputChange(ev) {
    const files = ev.target.files || [];
    addFilesToPreview(files);
}

// attach handler to initial input
document.getElementById('photos').addEventListener('change', handleFileInputChange);

// Handle dynamic added inputs
document.getElementById('add-photo-input').addEventListener('click', function(){
    const inputsWrap = document.getElementById('photos-inputs');
    const currentInputs = inputsWrap.querySelectorAll('input[type=file]');
    // allow up to 4 separate file inputs (but total images max 8) - keep simple
    if (currentInputs.length >= 4) return;
    const inp = document.createElement('input');
    inp.type = 'file'; inp.name = 'photos[]'; inp.accept = 'image/*'; inp.className = 'form-control mb-2';
    // attach change handler
    inp.addEventListener('change', handleFileInputChange);
    inputsWrap.appendChild(inp);
});

function updatePhotosCount(){
	const preview = document.getElementById('photos-preview');
	const count = preview.querySelectorAll('img').length;
	document.getElementById('photos-count').textContent = count + ' selected';
}

document.getElementById('video').addEventListener('change', function(e){
	const container = document.getElementById('video-preview');
	container.innerHTML = '';
	const file = this.files[0];
	if (!file) return;
	if (!file.type.startsWith('video/')) return;
	const url = URL.createObjectURL(file);
	const video = document.createElement('video');
	video.src = url; video.controls = true; video.style.maxWidth='100%'; video.style.maxHeight='320px';
	container.appendChild(video);
});

// Initialize photos count for existing images
document.addEventListener('DOMContentLoaded', function(){
	if (typeof updatePhotosCount === 'function') updatePhotosCount();
});
</script>

<?php
// If a new post was just created, fetch it and show inside a modal
if (!empty($_GET['created'])):
	$createdId = (int)$_GET['created'];
	require_once __DIR__ . '/../controllers/index-controller.php';
	$newPost = IndexController::getPostById($createdId);
	if ($newPost):
		$newPhotos = IndexController::getPostPhotos($createdId);
		// getPostVideos may exist; guard in case
		$newVideos = method_exists('\IndexController', 'getPostVideos') ? IndexController::getPostVideos($createdId) : [];
?>

<!-- Modal markup for new post (minimal success message) -->
<div class="modal fade" id="newPostModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-sm modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Success</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-center">
				<p class="mb-0">Post Created Successfully!</p>
			</div>
			<div class="modal-footer">
				<a href="./index.php" class="btn btn-primary">Go to Feed</a>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
	var modalEl = document.getElementById('newPostModal');
	if (modalEl) {
		var modal = new bootstrap.Modal(modalEl);
		modal.show();
	}
});
</script>

<?php
	endif;
endif;
?>

