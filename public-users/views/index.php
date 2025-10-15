<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../controllers/index-controller.php';

$pageTitle = 'Community Feed';
// Composer display name (ensure we use the same session key used elsewhere)
$displayName = !empty($_SESSION['user']['full_name']) ? $_SESSION['user']['full_name'] : 'Share something';
$userId = $_SESSION['user']['id'] ?? null;
// Composer avatar resolved from stored profile_photo
if (!function_exists('resolve_profile_photo')) { include __DIR__ . '/../include/profile_helpers.php'; }
$composerAvatar = resolve_profile_photo($_SESSION['user']['profile_photo'] ?? null);

// Get recent posts from database
$posts = IndexController::getRecentPosts(20);
?>

<?php
// Helper to resolve media paths to URLs similar to resolve_profile_photo
function resolve_media_path(?string $path): string {
    if (empty($path)) return '../../assets/images/placeholder.png';
    $p = trim($path);
    if (preg_match('#^https?://#i', $p)) return $p;
    $normalized = str_replace('\\', '/', $p);
    $pos = stripos($normalized, 'storage/');
    if ($pos !== false) {
        $sub = substr($normalized, $pos);
        return '../../' . ltrim($sub, '/');
    }
    $normalized = preg_replace('#^(\.{1,2}/)+#', '', $normalized);
    $normalized = ltrim($normalized, '/');
    if (stripos($normalized, 'storage/') === 0) return '../../' . $normalized;
    if (stripos($normalized, 'uploads/') === 0) return '../../storage/' . ltrim($normalized, '/');
    return '../../' . $normalized;
}
?>

<style>
/* Post media styling: single image fills width with controlled height and object-fit; grid thumbs are uniform */
.post-media-single img {
    width: 100%;
    max-height: 420px;
    height: 420px;
    object-fit: cover;
    display: block;
    border-radius: 8px;
    aspect-ratio: 1;
}

.post-media-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.post-media-grid img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    aspect-ratio: 1;
}

@media (min-width: 992px) {
    .post-media-grid img {
        height: 200px;
    }
}

.play-overlay i {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

#imageModal .modal-backdrop, #videoModal .modal-backdrop {
    background-color: rgba(0, 0, 0, 0.8);
}
</style>

<?php
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

<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Left sidebar: shortcuts -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
        <!-- Center: composer and feed -->
        <div class="col-12 col-lg-6"
            style="max-height:862px; overflow-y:auto; overflow-x:hidden; scrollbar-width:none; -ms-overflow-style:none;">
            <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars((string)$flashSuccess, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>
            <?php if ($flashError): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars((string)$flashError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>

            <!-- Composer -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <img src="<?php echo htmlspecialchars($composerAvatar, ENT_QUOTES, 'UTF-8'); ?>"
                            class="rounded-circle me-3 object-fit-cover" width="44" height="44"
                            alt="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>'s avatar"
                            style="object-fit:cover;" />
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal"
                            class="form-control text-start text-muted text-decoration-none"
                            style="text-decoration:none;">
                            <i class="ti ti-edit me-2"
                                aria-hidden="true"></i><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </div>
                    <div class="d-flex gap-2 mt-3 composer-actions btn-stack-sm">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal"
                            class="btn btn-light border"><i class="ti ti-photo me-1 text-success"></i> Photo</a>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal"
                            class="btn btn-light border"><i class="ti ti-video me-1 text-danger"></i> Video</a>
                    </div>
                </div>
            </div>

            <!-- Feed items from database -->
            <?php if (empty($posts)): ?>
            <div class="card mb-3">
                <div class="card-body text-center py-5">
                    <i class="ti ti-mood-empty" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">No posts yet. Be the first to share something!</p>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createPostModal" class="btn btn-primary mt-2">
                        <i class="ti ti-plus me-1"></i> Create Post
                    </a>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($posts as $post): 
                    // Get post photos
                    $photos = IndexController::getPostPhotos($post['id']);
                    
                    // Format date
                    $postDate = new DateTime($post['created_at']);
                    $now = new DateTime();
                    $interval = $now->diff($postDate);
                    
                    if ($interval->d == 0) {
                        if ($interval->h == 0) {
                            $timeAgo = $interval->i . ' min ago';
                        } else {
                            $timeAgo = $interval->h . ' hr' . ($interval->h > 1 ? 's' : '') . ' ago';
                        }
                    } elseif ($interval->d == 1) {
                        $timeAgo = 'Yesterday';
                    } elseif ($interval->d < 7) {
                        $timeAgo = $interval->d . ' days ago';
                    } else {
                        $timeAgo = $postDate->format('M d, Y');
                    }
                    
                    // Get profile photo or default using helper
                    $profilePhoto = resolve_profile_photo($post['profile_photo'] ?? null);
                ?>

            <!-- Single post card -->
            <div class="card mb-3">
                <!-- Make only this card body scrollable -->
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <a href="./profile.php?user_id=<?php echo urlencode($post['user_id']); ?>">
                            <img src="<?php echo htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>"
                                class="rounded-circle me-2 object-fit-cover" width="36" height="36"
                                style="object-fit: cover; aspect-ratio: 1/1; min-width:36px; min-height:36px; max-width:36px; max-height:36px;"
                                alt="Profile picture" onerror="this.src='../../assets/images/profile/user-1.jpg'" />
                        </a>
                        <div>
                            <a href="./profile.php?user_id=<?php echo urlencode($post['user_id']); ?>"
                                class="fw-bold text-decoration-none text-dark">
                                <?php echo htmlspecialchars($post['full_name']); ?>
                            </a>
                            <div class="text-muted small"><?php echo $timeAgo; ?></div>
                        </div>
                    </div>

                    <?php if (!empty($post['content'])): ?>
                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <?php endif; ?>

                    <?php
                        $videos = IndexController::getPostVideos($post['id']);
                    ?>

                    <?php if (!empty($photos)): ?>
                    <?php if (count($photos) == 1): ?>
                    <div class="post-media-single mb-3">
                        <img src="<?php echo htmlspecialchars(resolve_media_path($photos[0]['photo_path']), ENT_QUOTES, 'UTF-8'); ?>"
                            alt="Post photo" onerror="this.style.display='none'"
                            onclick="openImageModal([<?php echo htmlspecialchars(json_encode(resolve_media_path($photos[0]['photo_path'])), ENT_QUOTES, 'UTF-8'); ?>])"
                            style="cursor:pointer;" />
                    </div>
                    <?php else: ?>
                    <div class="post-media-grid mb-3"
                        onclick="openImageModal(<?php echo htmlspecialchars(json_encode(array_map(function($p){return resolve_media_path($p['photo_path']);}, $photos)), ENT_QUOTES, 'UTF-8'); ?>)"
                        style="cursor:pointer;">
                        <?php foreach (array_slice($photos, 0, 4) as $index => $photo): ?>
                        <div style="position:relative;">
                            <img src="<?php echo htmlspecialchars(resolve_media_path($photo['photo_path']), ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Post photo" onerror="this.style.display='none'" />
                            <?php if ($index == 3 && count($photos) > 4): ?>
                            <div
                                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 rounded">
                                <span class="text-white fs-4">+<?php echo count($photos) - 4; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($videos)): ?>
                    <?php foreach ($videos as $v): ?>
                    <div class="video-container position-relative mb-3" onclick="openVideoModalWithPause('<?php echo htmlspecialchars(resolve_media_path($v['video_path']), ENT_QUOTES, 'UTF-8'); ?>', this)">
                        <video controls class="w-100" style="max-height:420px; aspect-ratio: 1; object-fit: cover;" controlslist="nodownload">
                            <source
                                src="<?php echo htmlspecialchars(resolve_media_path($v['video_path']), ENT_QUOTES, 'UTF-8'); ?>" />
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between post-actions-sm mt-2">
                        <div class="action-group d-flex flex-wrap">
                            <a href="./post_view.php?id=<?php echo $post['id']; ?>"
                                class="btn btn-light border me-1 mb-1">
                                <i class="ti ti-thumb-up"></i>
                                <span class="d-none d-sm-inline">Like</span>
                                <?php if ($post['reaction_count'] > 0): ?>
                                <span
                                    class="badge bg-primary rounded-pill ms-1"><?php echo $post['reaction_count']; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="./post_view.php?id=<?php echo $post['id']; ?>"
                                class="btn btn-light border me-1 mb-1">
                                <i class="ti ti-message-circle"></i>
                                <span class="d-none d-sm-inline">Comment</span>
                                <?php if ($post['comment_count'] > 0): ?>
                                <span
                                    class="badge bg-primary rounded-pill ms-1"><?php echo $post['comment_count']; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="./post_view.php?id=<?php echo $post['id']; ?>" class="btn btn-light border mb-1">
                                <i class="ti ti-share"></i>
                                <span class="d-none d-sm-inline">Share</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End single post card -->

            <?php endforeach; ?>
            <?php endif; ?>
            <!-- Create Post Modal -->
            <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createPostModalLabel">Create Post</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" action="../controllers/CreatePostController.php"
                                enctype="multipart/form-data">
                                <input type="hidden" name="action" value="create" />
                                <div class="mb-3">
                                    <label for="content" class="form-label">What's on your mind?</label>
                                    <textarea id="content" name="content" class="form-control" rows="4"
                                        placeholder="Put something here, or leave empty and attach images/videos."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Media (drag and drop images or videos, max 8 images, 1
                                        video)</label>
                                    <div id="drop-zone"
                                        class="border border-dashed border-secondary rounded p-4 text-center"
                                        style="min-height: 200px;">
                                        <p>Drag and drop images or videos here or click to select</p>
                                        <input type="file" id="media" name="media[]" accept="image/*,video/*" multiple
                                            style="display: none;" />
                                    </div>
                                    <div class="d-flex gap-2 mb-2">
                                        <small id="media-count" class="text-muted">0 selected</small>
                                    </div>
                                    <div id="media-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                </div>
                                <small class="text-muted">You can add multiple images. Max recommended: 8 images. One
                                    video per post.</small>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Post</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Modal -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0">
                        <button class="btn-close position-absolute top-0 end-0 m-2 text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="modal-body p-0">
                            <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner" id="carousel-inner"></div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Video Modal -->
            <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0">
                        <button class="btn-close position-absolute top-0 end-0 m-2 text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="modal-body p-0 text-center">
                            <video id="modalVideo" controls class="w-100" style="max-height: 70vh;">
                                <source id="modalVideoSource" src="" />
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
            </div>

            <?php include __DIR__ . '/../include/footer.php'; ?>

            <script>
            const MAX_MEDIA = 9; // 8 images + 1 video
            const preview = document.getElementById('media-preview');
            const mediaCountLabel = document.getElementById('media-count');
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('media');

            // Track seen previews to avoid duplicates
            const previewKeys = new Set();

            let dragCounter = 0;
            let selectedFiles = [];
            let fileMap = new Map();

            function updateFileInput() {
                const dt = new DataTransfer();
                selectedFiles.forEach(f => dt.items.add(f));
                fileInput.files = dt.files;
            }

            function addImageElement(src, key, file = null) {
                if (preview.querySelectorAll('.media-container').length >= MAX_MEDIA) return false;
                if (key && previewKeys.has(key)) return false;
                const container = document.createElement('div');
                container.className = 'media-container position-relative';
                const img = document.createElement('img');
                img.src = src;
                img.style.width = '120px';
                img.style.height = '120px';
                img.style.objectFit = 'cover';
                img.className = 'rounded';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
                removeBtn.textContent = '×';
                removeBtn.onclick = function() {
                    removeMedia(this);
                };
                container.appendChild(img);
                container.appendChild(removeBtn);
                preview.appendChild(container);
                if (key) previewKeys.add(key);
                updateMediaCount();
                return true;
            }

            function addVideoElement(src, key, file = null) {
                if (preview.querySelectorAll('.media-container').length >= MAX_MEDIA) return false;
                if (key && previewKeys.has(key)) return false;
                const container = document.createElement('div');
                container.className = 'media-container position-relative';
                const video = document.createElement('video');
                video.src = src;
                video.style.width = '120px';
                video.style.height = '120px';
                video.style.objectFit = 'cover';
                video.className = 'rounded';
                video.muted = true;
                video.loop = true;
                video.preload = 'metadata';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
                removeBtn.textContent = '×';
                removeBtn.onclick = function() {
                    removeMedia(this);
                };
                container.appendChild(video);
                container.appendChild(removeBtn);
                preview.appendChild(container);
                if (key) previewKeys.add(key);
                updateMediaCount();
                return true;
            }

            function removeMedia(btn) {
                const container = btn.parentElement;
                const media = container.querySelector('img, video');
                const src = media.src;
                if (src.startsWith('blob:')) {
                    for (let [file, index] of fileMap) {
                        if (URL.createObjectURL(file) === src) {
                            selectedFiles.splice(index, 1);
                            fileMap.delete(file);
                            // Update indices
                            fileMap.clear();
                            selectedFiles.forEach((f, i) => fileMap.set(f, i));
                            break;
                        }
                    }
                }
                container.remove();
                updateFileInput();
                updateMediaCount();
            }

            function updateMediaCount() {
                const count = preview.querySelectorAll('.media-container').length;
                mediaCountLabel.textContent = count + (count === 1 ? ' selected' : ' selected');
            }

            dropZone.addEventListener('click', () => {
                fileInput.click();
            });

            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
            });

            dropZone.addEventListener('dragenter', (e) => {
                e.preventDefault();
                dragCounter++;
                dropZone.classList.add('border-primary');
            });

            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dragCounter--;
                if (dragCounter === 0) {
                    dropZone.classList.remove('border-primary');
                }
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dragCounter = 0;
                dropZone.classList.remove('border-primary');
                const files = Array.from(e.dataTransfer.files);
                files.forEach((file) => {
                    selectedFiles.push(file);
                    fileMap.set(file, selectedFiles.length - 1);
                    const url = URL.createObjectURL(file);
                    if (file.type.startsWith('image/')) {
                        addImageElement(url, file.name, file);
                    } else if (file.type.startsWith('video/')) {
                        addVideoElement(url, file.name, file);
                    }
                });
                updateFileInput();
            });

            fileInput.addEventListener('change', (event) => {
                const files = Array.from(event.target.files);
                files.forEach((file) => {
                    selectedFiles.push(file);
                    fileMap.set(file, selectedFiles.length - 1);
                    const url = URL.createObjectURL(file);
                    if (file.type.startsWith('image/')) {
                        addImageElement(url, file.name, file);
                    } else if (file.type.startsWith('video/')) {
                        addVideoElement(url, file.name, file);
                    }
                });
                updateFileInput();
                updateMediaCount();
                fileInput.value = '';
            });

            // Image modal script
            function openImageModal(images) {
                const carouselInner = document.getElementById('carousel-inner');
                carouselInner.innerHTML = '';
                images.forEach((src, index) => {
                    const item = document.createElement('div');
                    item.className = 'carousel-item' + (index === 0 ? ' active' : '');
                    item.innerHTML = `<img src="${src}" class="d-block" style="max-width: 100%; max-height: 70vh; object-fit: contain; margin: 0 auto;" alt="Image">`;
                    carouselInner.appendChild(item);
                });
                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                modal.show();
            }

            // Video modal script
            function openVideoModal(src) {
                // Pause all videos to prevent double play
                document.querySelectorAll('video').forEach(v => v.pause());
                const modalVideoSource = document.getElementById('modalVideoSource');
                modalVideoSource.src = src;
                const modalVideo = document.getElementById('modalVideo');
                modalVideo.load(); // Reload the video
                const modal = new bootstrap.Modal(document.getElementById('videoModal'));
                modal.show();
            }

            function openVideoModalWithPause(src, container) {
                const video = container.querySelector('video');
                video.pause();
                openVideoModal(src);
            }

            // Video overlay script
            document.querySelectorAll('.video-container').forEach(container => {
                const video = container.querySelector('video');
                const overlay = container.querySelector('.play-overlay');
                const icon = overlay.querySelector('i');
                let isHovering = false;

                container.addEventListener('mouseenter', () => {
                    isHovering = true;
                    if (video.paused || video.ended) {
                        overlay.style.display = 'flex';
                    }
                });
                container.addEventListener('mouseleave', () => {
                    isHovering = false;
                    overlay.style.display = 'none';
                });

                video.addEventListener('playing', () => {
                    overlay.style.display = 'none';
                });
                video.addEventListener('pause', () => {
                    if (isHovering) {
                        overlay.style.display = 'flex';
                    }
                    icon.className = 'ti ti-player-play text-white';
                });
                video.addEventListener('ended', () => {
                    if (isHovering) {
                        overlay.style.display = 'flex';
                    }
                    icon.className = 'ti ti-player-play text-white';
                });
                overlay.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                });
                video.addEventListener('click', () => {
                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                });
            });
            </script>