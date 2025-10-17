document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	const dropZone = document.getElementById('drop-zone');
	const input = document.getElementById('media');
	const selectBtn = document.getElementById('select-media-btn');
	if (!dropZone || !input) return;

	// Find persistent preview container if present, otherwise create one
	let previewContainer = document.getElementById('media-previews') || dropZone.querySelector('.media-previews');
	if (!previewContainer) {
		previewContainer = document.createElement('div');
		previewContainer.className = 'media-previews mt-3 d-flex flex-wrap gap-2';
		dropZone.appendChild(previewContainer);
	}

	// Ensure initial placeholder
	if (previewContainer && previewContainer.children.length === 0) {
		previewContainer.innerHTML = '<div class="text-muted small">No files selected</div>';
	}

	// currentFiles stores objects: { key: string, file: File }
	let currentFiles = [];
	const objectURLs = new Map(); // key -> objectURL

	function makeKey(file) {
		return (file.name || '') + '|' + (file.size || 0) + '|' + (file.type || '');
	}

	function countKinds(entries) {
		let images = 0, videos = 0;
		for (const e of entries) {
			const f = e.file;
			if (!f || !f.type) continue;
			if (f.type.startsWith('image/')) images++;
			if (f.type.startsWith('video/')) videos++;
		}
		return { images, videos };
	}

	function updateInputFiles() {
		const dt = new DataTransfer();
		for (const e of currentFiles) dt.items.add(e.file);
		input.files = dt.files;
	}

	function renderPreviews() {
		if (!previewContainer) return;
		previewContainer.innerHTML = '';
		if (currentFiles.length === 0) {
			previewContainer.innerHTML = '<div class="text-muted small">No files selected</div>';
			return;
		}

		currentFiles.forEach((entry, idx) => {
			const file = entry.file;
			const key = entry.key;
			const wrapper = document.createElement('div');
			wrapper.className = 'position-relative border rounded overflow-hidden';
			wrapper.style.width = '110px';
			wrapper.style.height = '110px';
			wrapper.style.display = 'inline-block';
			wrapper.style.background = '#f8f9fa';

			let url = objectURLs.get(key);
			if (!url) {
				url = URL.createObjectURL(file);
				objectURLs.set(key, url);
			}

			if (file.type && file.type.startsWith('image/')) {
				const img = document.createElement('img');
				img.src = url;
				img.style.width = '100%';
				img.style.height = '100%';
				img.style.objectFit = 'cover';
				wrapper.appendChild(img);
			} else if (file.type && file.type.startsWith('video/')) {
				const vid = document.createElement('video');
				vid.src = url;
				vid.muted = true;
				vid.playsInline = true;
				vid.style.width = '100%';
				vid.style.height = '100%';
				vid.style.objectFit = 'cover';
				vid.controls = false;
				wrapper.appendChild(vid);
				const play = document.createElement('div');
				play.innerHTML = '\u25BA';
				play.style.position = 'absolute';
				play.style.left = '50%';
				play.style.top = '50%';
				play.style.transform = 'translate(-50%,-50%)';
				play.style.color = 'rgba(255,255,255,0.9)';
				play.style.fontSize = '24px';
				wrapper.appendChild(play);
			}

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'btn btn-sm btn-danger';
			removeBtn.style.position = 'absolute';
			removeBtn.style.top = '4px';
			removeBtn.style.right = '4px';
			removeBtn.style.zIndex = '5';
			removeBtn.innerHTML = '×';
			removeBtn.onclick = function (e) {
				e.stopPropagation();
				// revoke and cleanup
				if (objectURLs.has(key)) {
					URL.revokeObjectURL(objectURLs.get(key));
					objectURLs.delete(key);
				}
				currentFiles.splice(idx, 1);
				renderPreviews();
				updateInputFiles();
			};

			wrapper.appendChild(removeBtn);
			previewContainer.appendChild(wrapper);
		});
	}

	function addFiles(filesList) {
		const incoming = Array.from(filesList || []);
		if (incoming.length === 0) return;
		console.log('[composer] addFiles incoming:', incoming.length);

		// compute existing counts
		const existing = countKinds(currentFiles);
		let toAdd = [];

		for (const f of incoming) {
			const key = makeKey(f);
			if (currentFiles.some(e => e.key === key)) continue; // skip dup
			// check limits
			const isImage = f.type && f.type.startsWith('image/');
			const isVideo = f.type && f.type.startsWith('video/');
			if (isVideo && existing.videos + toAdd.filter(t => t.file.type.startsWith('video/')).length >= 1) {
				// skip extra videos
				console.warn('[composer] skipping extra video:', f.name);
				continue;
			}
			if (isImage && existing.images + toAdd.filter(t => t.file.type.startsWith('image/')).length >= 8) {
				console.warn('[composer] skipping extra image:', f.name);
				continue;
			}
			toAdd.push({ key: key, file: f });
		}

		if (toAdd.length > 0) {
			currentFiles = currentFiles.concat(toAdd);
			renderPreviews();
			updateInputFiles();
		}
	}

	// Click on drop zone opens file picker (but avoid when clicking inner buttons)
	dropZone.addEventListener('click', function (e) {
		if (e.target && (e.target.tagName.toLowerCase() === 'button' || e.target.closest('button'))) return; // let buttons inside work
		input.click();
	});

	// Also bind visible Select button (input overlay already handles clicks)
	if (selectBtn) {
		selectBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			// do not call input.click() because the input sits over the button and will receive the click
		});
	}

	input.addEventListener('change', function () {
		if (!input.files || input.files.length === 0) return;
		console.log('[composer] input.change - files selected:', input.files.length);
		addFiles(input.files);
		// do not clear input.value here
	});

	// drag & drop
	dropZone.addEventListener('dragover', function (e) {
		e.preventDefault();
		dropZone.classList.add('drag-over');
	});
	dropZone.addEventListener('dragleave', function (e) {
		e.preventDefault();
		dropZone.classList.remove('drag-over');
	});
	dropZone.addEventListener('drop', function (e) {
		e.preventDefault();
		dropZone.classList.remove('drag-over');
		if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
			addFiles(e.dataTransfer.files);
		}
	});

	// expose for debugging (optional)
	window._postComposerFiles = currentFiles;
});