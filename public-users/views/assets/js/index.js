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