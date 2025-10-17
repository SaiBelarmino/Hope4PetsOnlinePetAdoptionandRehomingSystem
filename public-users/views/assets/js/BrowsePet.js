// Inject minimal CSS for one-line truncate
(function(){
    if (document.getElementById('browsepet-inline-styles')) return;
    var css = '\n.one-line-truncate { display: inline-block; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }\n';
    var style = document.createElement('style');
    style.id = 'browsepet-inline-styles';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);
})();

function togglePetDescription(id) {
    var shortEl = document.getElementById('desc-short-' + id);
    var fullEl = document.getElementById('desc-full-' + id);
    var toggleLink = document.getElementById('desc-toggle-' + id);
    if (!shortEl || !fullEl || !toggleLink) return;
    if (fullEl.classList.contains('d-none')) {
        shortEl.classList.add('d-none');
        fullEl.classList.remove('d-none');
        toggleLink.textContent = 'See less';
    } else {
        shortEl.classList.remove('d-none');
        fullEl.classList.add('d-none');
        toggleLink.textContent = 'See more';
    }
}

function initDescToggles() {
    var shorts = document.querySelectorAll('span[id^="desc-short-"]');
    shorts.forEach(function(shortEl){
        try {
            var id = shortEl.id.replace('desc-short-','');
            var toggleLink = document.getElementById('desc-toggle-' + id);
            if (!toggleLink) return;
            // Ensure class applied
            shortEl.classList.add('one-line-truncate');
            // If content overflows horizontally, show toggle
            // Use scrollWidth/clientWidth which is reliable for single-line truncation
            if (shortEl.scrollWidth > shortEl.clientWidth) {
                toggleLink.classList.remove('d-none');
            } else {
                toggleLink.classList.add('d-none');
            }
        } catch (e) {
            // silent
            console.error(e);
        }
    });
}

// Run initialization immediately if document already loaded, otherwise wait for DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDescToggles);
} else {
    initDescToggles();
}
