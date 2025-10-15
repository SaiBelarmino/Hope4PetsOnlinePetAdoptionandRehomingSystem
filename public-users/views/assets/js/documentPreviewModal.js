(function() {
    var APP_BASE_LOCAL = typeof APP_BASE !== 'undefined' ? APP_BASE : '';
    var modal = document.getElementById('documentPreviewModal');
    var wrap = document.getElementById('previewWrap');
    var currentEl = null; // img or iframe
    var scale = 1;
    var minScale = 0.25;
    var maxScale = 5;
    var translate = {
        x: 0,
        y: 0
    };
    var dragging = false;
    var dragStart = {
        x: 0,
        y: 0
    };

    function clearPreview() {
        wrap.innerHTML = '';
        currentEl = null;
        scale = 1;
        translate = {
            x: 0,
            y: 0
        };
        updateTransform();
    }

    function updateTransform() {
        if (!currentEl) return;
        currentEl.style.transform = 'translate(' + translate.x + 'px,' + translate.y + 'px) scale(' + scale + ')';
    }

    function loadPreview(finalUrl) {
        clearPreview();
        var ext = (finalUrl.split('.').pop() || '').toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1) {
            var img = document.createElement('img');
            img.className = 'preview-media';
            img.onload = function() {
                centerFit();
            };
            img.src = finalUrl;
            wrap.appendChild(img);
            currentEl = img;
            attachPanHandlers(img);
        } else if (ext === 'pdf') {
            var iframe = document.createElement('iframe');
            iframe.src = finalUrl;
            iframe.className = 'preview-media';
            wrap.appendChild(iframe);
            currentEl = iframe;
            // panning/zoom on iframe will use CSS transform too
            attachPanHandlers(iframe);
        } else {
            wrap.innerHTML = '<p class="small text-muted">Cannot preview this file type. <a href="' + finalUrl +
                '" target="_blank">Open in new tab</a></p>';
        }
    }

    function centerFit() {
        // reset position and scale
        scale = 1;
        translate = {
            x: 0,
            y: 0
        };
        updateTransform();
    }

    function zoomBy(delta) {
        var old = scale;
        scale = Math.min(maxScale, Math.max(minScale, scale + delta));
        if (scale === old) return;
        updateTransform();
    }

    // mouse wheel zoom
    function onWheel(e) {
        if (!currentEl) return;
        e.preventDefault();
        var delta = (e.deltaY > 0) ? -0.1 : 0.1;
        zoomBy(delta);
    }

    // pan handlers
    function attachPanHandlers(el) {
        el.style.cursor = 'grab';
        el.addEventListener('mousedown', function(e) {
            dragging = true;
            dragStart.x = e.clientX - translate.x;
            dragStart.y = e.clientY - translate.y;
            el.style.cursor = 'grabbing';
            e.preventDefault();
        });
        window.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            translate.x = e.clientX - dragStart.x;
            translate.y = e.clientY - dragStart.y;
            updateTransform();
        });
        window.addEventListener('mouseup', function(e) {
            if (dragging) {
                dragging = false;
                if (currentEl) currentEl.style.cursor = 'grab';
            }
        });

        // touch
        var touchStartPos = null;
        var lastDist = null;
        el.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                touchStartPos = {
                    x: e.touches[0].clientX - translate.x,
                    y: e.touches[0].clientY - translate.y
                };
            } else if (e.touches.length === 2) {
                lastDist = distance(e.touches[0], e.touches[1]);
            }
        }, {
            passive: false
        });
        el.addEventListener('touchmove', function(e) {
            e.preventDefault();
            if (e.touches.length === 1 && touchStartPos) {
                translate.x = e.touches[0].clientX - touchStartPos.x;
                translate.y = e.touches[0].clientY - touchStartPos.y;
                updateTransform();
            } else if (e.touches.length === 2) {
                var d = distance(e.touches[0], e.touches[1]);
                if (lastDist) {
                    var dd = (d - lastDist) / 200;
                    zoomBy(dd);
                }
                lastDist = d;
            }
        }, {
            passive: false
        });
        el.addEventListener('touchend', function(e) {
            touchStartPos = null;
            lastDist = null;
        });

        // wheel on wrap
        wrap.addEventListener('wheel', onWheel, {
            passive: false
        });
    }

    function distance(a, b) {
        var dx = a.clientX - b.clientX;
        var dy = a.clientY - b.clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // toolbar buttons
    document.getElementById('zoomInBtn').addEventListener('click', function() {
        zoomBy(0.25);
    });
    document.getElementById('zoomOutBtn').addEventListener('click', function() {
        zoomBy(-0.25);
    });
    document.getElementById('zoomResetBtn').addEventListener('click', function() {
        scale = 1;
        translate = {
            x: 0,
            y: 0
        };
        updateTransform();
    });

    // open function exposed globally
    window.openDocumentModal = function(url) {
        try {
            var finalUrl = url;
            try {
                if (typeof url === 'string' && url.indexOf('/storage/') === 0 && APP_BASE_LOCAL) finalUrl =
                    APP_BASE_LOCAL + url;
            } catch (e) {}
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            loadPreview(finalUrl);
        } catch (e) {
            console.error(e);
            window.open(url, '_blank');
        }
    };

    window.closeDocumentModal = function() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            clearPreview();
        }
    };
})();