<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: create_post.php
 * Tables: posts, post_photos, pets (optional association)
 * Expected Variables:
 *  - $pets (optional) => user pets list for attaching to a post
 *  - $flash (optional)
 */
$pageTitle = 'Create Post';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./index.php" class="btn btn-outline-secondary"><i class="ti ti-arrow-left"></i> Feed</a>
      </div>
      <?php if(!empty($flash['message'])): ?><div class="alert alert-<?php echo htmlspecialchars($flash['type'] ?? 'info'); ?>"><?php echo htmlspecialchars($flash['message']); ?></div><?php endif; ?>
      <div class="card">
        <div class="card-body">
          <form action="../controllers/create-post-controller.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Content</label>
          <textarea name="content" class="form-control" rows="4" placeholder="What's new?" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Attach Photos</label>
           <input type="file" name="photos[]" id="photoInput" class="form-control d-none" multiple accept="image/*">
           <span id="dropText">Drag & drop images here or click to select</span>
           <!-- Visible choose file button to complement drag & drop -->
           <div class="mt-2 d-flex gap-2">
             <button type="button" id="chooseFilesBtn" class="btn btn-outline-primary btn-sm">Choose images</button>
             <button type="button" id="chooseVideoBtn" class="btn btn-outline-secondary btn-sm">Attach video</button>
           </div>
          <div id="photoPreview" class="row g-2 mt-2"></div>
          <div id="videoPreview" class="mt-2"></div>
          <div id="photoLimitWarning" class="text-danger small mt-1" style="display:none;"></div>
          <small class="text-muted">You can upload up to 5 images. Each max 5MB. Videos up to 50MB (mp4/webm/ogg).</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Link a Pet (optional)</label>
          <select name="pet_id" class="form-select">
            <option value="">None</option>
            <?php if(!empty($pets)) foreach($pets as $p): ?>
              <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name'].' • '.ucfirst($p['species'])); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <a href="./index.php" class="btn btn-light border">Cancel</a>
          <button class="btn btn-primary"><i class="ti ti-send"></i> Post</button>
        </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Tips</h6>
          <p class="small text-muted mb-0">Attach clear photos. Link a pet to boost visibility.</p>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./community.php" class="btn btn-sm btn-light border">Community</a>
            <a href="./my_posts.php" class="btn btn-sm btn-light border">My Posts</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
<script>
  (function(){
  const chooseBtn = document.getElementById('chooseFilesBtn');
  const chooseVideoBtn = document.getElementById('chooseVideoBtn');
  const photoInput = document.getElementById('photoInput');
  // create a hidden video input for single video
  const videoInput = document.createElement('input');
  videoInput.type = 'file';
  videoInput.name = 'video';
  videoInput.id = 'videoInput';
  videoInput.accept = 'video/*';
  videoInput.className = 'd-none';
  document.querySelector('form').appendChild(videoInput);
  const preview = document.getElementById('photoPreview');
  const videoPreview = document.getElementById('videoPreview');
  const warning = document.getElementById('photoLimitWarning');
  const dropText = document.getElementById('dropText');

  if (chooseBtn) chooseBtn.addEventListener('click', () => photoInput.click());
  if (chooseVideoBtn) chooseVideoBtn.addEventListener('click', () => videoInput.click());

    // Basic preview + limits
  photoInput.addEventListener('change', (e) => {
      const files = Array.from(photoInput.files || []);
      // Limit to 5 files
      if (files.length > 5) {
        warning.textContent = 'You can only upload up to 5 images.';
        warning.style.display = '';
        const dt = new DataTransfer();
        files.slice(0,5).forEach(f => dt.items.add(f));
        photoInput.files = dt.files;
      } else {
        warning.style.display = 'none';
      }
      preview.innerHTML = '';
      Array.from(photoInput.files || []).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        if (file.size > 5 * 1024 * 1024) { // 5MB limit
          const s = document.createElement('div');
          s.className = 'col-12';
          s.innerHTML = '<small class="text-danger">"'+file.name+'" is larger than 5MB and will not be uploaded.</small>';
          preview.appendChild(s);
          return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
          const col = document.createElement('div');
          col.className = 'col-4 col-md-3';
          const img = document.createElement('img');
          img.src = ev.target.result;
          img.className = 'img-fluid rounded';
          img.alt = 'Preview';
          col.appendChild(img);
          preview.appendChild(col);
        };
        reader.readAsDataURL(file);
      });
    });

    // Video preview handler (single file)
    videoInput.addEventListener('change', (e) => {
      const file = videoInput.files && videoInput.files[0];
      videoPreview.innerHTML = '';
      if (!file) return;
      const allowed = ['video/mp4','video/webm','video/ogg','video/quicktime'];
      if (!allowed.includes(file.type)) {
        const el = document.createElement('div'); el.className='text-danger small'; el.textContent = 'Unsupported video type.'; videoPreview.appendChild(el); return;
      }
      if (file.size > 50 * 1024 * 1024) {
        const el = document.createElement('div'); el.className='text-danger small'; el.textContent = 'Video exceeds 50MB limit.'; videoPreview.appendChild(el); return;
      }
      const url = URL.createObjectURL(file);
      const vid = document.createElement('video');
      vid.src = url; vid.controls = true; vid.className = 'img-fluid rounded'; vid.style.maxHeight = '360px';
      videoPreview.appendChild(vid);
    });

    // Optional: quick drag & drop support on the dropText area
    const dropArea = dropText ? dropText.parentElement : null;
    if (dropArea) {
      dropArea.style.cursor = 'pointer';
      dropArea.addEventListener('dragover', (ev) => { ev.preventDefault(); dropArea.classList.add('bg-primary','text-white'); });
      dropArea.addEventListener('dragleave', (ev) => { ev.preventDefault(); dropArea.classList.remove('bg-primary','text-white'); });
      dropArea.addEventListener('drop', (ev) => {
        ev.preventDefault(); dropArea.classList.remove('bg-primary','text-white');
        const files = Array.from(ev.dataTransfer.files || []);
        if (files.length) {
          const dt = new DataTransfer();
          files.slice(0,5).forEach(f => dt.items.add(f));
          photoInput.files = dt.files;
          photoInput.dispatchEvent(new Event('change'));
        }
      });
      dropArea.addEventListener('click', () => photoInput.click());
    }
  })();
</script>
