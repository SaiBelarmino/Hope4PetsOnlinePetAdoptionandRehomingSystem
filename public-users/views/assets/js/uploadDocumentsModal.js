function openModal(id, extra) {
    if (id === 'uploadDocumentsModal' && extra) {
        try {
            const el = document.querySelector('#uploadDocumentsModal select[name="doc_type"]');
            if (el) el.value = decodeURIComponent(extra);
        } catch (e) {}
    }
    const m = document.getElementById(id);
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}
// removed modal opener; uploads are now inline in the Documents card
// single camera modal handler reused by page (if implemented)
function showCameraModal(prefix) {
    // find target input
    let input = null;
    try {
        if (prefix === 'optional_document') {
            input = document.querySelector('input[name="optional_document"]');
        } else {
            // required_docs[<prefix>]
            input = document.querySelector('input[name="required_docs[' + prefix + ']"]');
        }
    } catch (e) {
        input = null;
    }
    if (!input) {
        alert('File input not found for: ' + prefix);
        return;
    }
    // open camera modal
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    const captureBtn = document.getElementById('cameraCaptureBtn');
    const switchBtn = document.getElementById('cameraSwitchBtn');
    modal.dataset.targetInput = prefix;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // constraints prefer environment camera
    const constraints = {
        video: {
            facingMode: {
                ideal: 'environment'
            }
        },
        audio: false
    };
    // attempt to get media
    navigator.mediaDevices.getUserMedia(constraints).then(s => {
        modal._stream = s;
        video.srcObject = s;
        video.play();
    }).catch(err => {
        console.error('Camera error', err);
        $.notify('Cannot access camera: ' + (err && err.message ? err.message : err), {
            align: 'center',
            verticalAlign: 'top'
        });
        closeCameraModal();
    });
}

function closeCameraModal() {
    const modal = document.getElementById('cameraModal');
    if (!modal) return;
    const video = document.getElementById('cameraVideo');
    // stop stream
    if (modal._stream) {
        modal._stream.getTracks().forEach(t => t.stop());
        modal._stream = null;
    }
    video.pause();
    video.srcObject = null;
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function captureFromCamera() {
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    const prefix = modal.dataset.targetInput;
    if (!modal._stream) {
        $.notify('Camera not ready', {
            align: 'center',
            verticalAlign: 'top'
        });
        return;
    }
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth || 1280;
    canvas.height = video.videoHeight || 720;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(function(blob) {
        if (!blob) {
            $.notify('Capture failed', {
                align: 'center',
                verticalAlign: 'top'
            });
            return;
        }
        const filename = (prefix || 'capture') + '_' + Date.now() + '.jpg';
        try {
            const file = new File([blob], filename, {
                type: 'image/jpeg'
            });
            const dt = new DataTransfer();
            // find target input again
            let input;
            if (prefix === 'optional_document') input = document.querySelector(
                'input[name="optional_document"]');
            else input = document.querySelector('input[name="required_docs[' + prefix + ']"]');
            if (!input) {
                $.notify('Target input not found for: ' + prefix, {
                    align: 'center',
                    verticalAlign: 'top'
                });
                closeCameraModal();
                return;
            }
            dt.items.add(file);
            input.files = dt.files;
            // update selected files info if exists
            const infoEl = document.getElementById('inlineSelectedFilesInfo');
            if (infoEl) infoEl.textContent = '1 file selected: ' + filename;
            closeCameraModal();
        } catch (e) {
            console.error('Assign file error', e);
            $.notify('Cannot assign captured photo to input in this browser.', {
                align: 'center',
                verticalAlign: 'top'
            });
        }
    }, 'image/jpeg', 0.9);
}