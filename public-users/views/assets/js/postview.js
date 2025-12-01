// postview.js - clean carousel initialization and UI handlers
document.addEventListener('DOMContentLoaded', function () {
  // ---- Caption See more / See less ----
  // REWRITE: simpler half-panel expansion with scroll
  var captionText = document.getElementById('captionText');
  var seeMoreBtn = document.getElementById('seeMoreBtn');
  var fadeOverlay = document.getElementById('fadeOverlay');
  var expanded = false;
  if (captionText) {
    var COLLAPSED = parseInt(captionText.getAttribute('data-collapsed-height') || '120', 10);
    var EXPANDED_MAX_ATTR = parseInt(captionText.getAttribute('data-expanded-max') || '260', 10);
    function expandedHeight(){
      var side = document.querySelector('.post-side');
      if(!side) return Math.min(EXPANDED_MAX_ATTR, captionText.scrollHeight);
      var headerH = side.querySelector('.p-3.border-bottom')?.offsetHeight || 0;
      var inputH = side.querySelector('.border-top.p-3')?.offsetHeight || 0;
      var reserveComments = 180; // keep comments visible
      var buffer = 32; // spacing
      var usable = side.clientHeight - headerH - inputH - reserveComments - buffer;
      if (usable < COLLAPSED + 40) usable = COLLAPSED + 40; // ensure there is scroll room
      var target = Math.min(usable, EXPANDED_MAX_ATTR, captionText.scrollHeight);
      return target;
    }
    function applyCollapsed(){
      captionText.style.maxHeight = COLLAPSED + 'px';
      captionText.style.overflowY = 'hidden'; // disable scroll when collapsed
      captionText.style.overflowX = 'hidden';
      if (fadeOverlay) fadeOverlay.style.display = '';
      if (seeMoreBtn) seeMoreBtn.textContent = 'See more';
      expanded = false;
    }
    function applyExpanded(){
      var h = expandedHeight();
      captionText.style.maxHeight = h + 'px';
      captionText.style.overflowY = 'auto'; // enable scroll when expanded
      captionText.style.overflowX = 'hidden';
      if (fadeOverlay) fadeOverlay.style.display = 'none';
      if (seeMoreBtn) seeMoreBtn.textContent = 'See less';
      expanded = true;
    }
    // initial
    applyCollapsed();
    if (captionText.scrollHeight <= COLLAPSED + 2) {
      if (seeMoreBtn) seeMoreBtn.style.display = 'none';
      if (fadeOverlay) fadeOverlay.style.display = 'none';
    }
    if (seeMoreBtn){
      seeMoreBtn.addEventListener('click', function(e){
        e.preventDefault();
        expanded ? applyCollapsed() : applyExpanded();
      });
    }
    window.addEventListener('resize', function(){ if(expanded) applyExpanded(); });
    // Prevent scroll chaining into comments when at bounds
    captionText.addEventListener('wheel', function(e){
      var atTop = captionText.scrollTop === 0;
      var atBottom = captionText.scrollTop + captionText.clientHeight >= captionText.scrollHeight;
      if ((e.deltaY < 0 && atTop) || (e.deltaY > 0 && atBottom)) {
        // stop propagation so parent doesn't steal scroll
        e.stopPropagation();
      }
    }, { passive: true });
    captionText.addEventListener('touchmove', function(e){ e.stopPropagation(); }, { passive: true });
  }

  // ---- Mobile header + caption reposition ----
  (function(){
    var root = document.querySelector('.post-root');
    var header = document.getElementById('postHeader');
    var caption = document.getElementById('captionContainer');
    var media = document.querySelector('.post-media');
    var side = document.querySelector('.post-side');
    if(!root || !header || !caption || !media || !side) return;
    var headerParent = side; // original
    var captionParent = side;
    function toMobile(){
      // order: header, caption, media, side (comments/input still inside side)
      if (header.parentElement !== root) root.insertBefore(header, root.firstChild);
      if (caption.parentElement !== root) root.insertBefore(caption, media);
    }
    function toDesktop(){
      // restore header & caption inside side
      if (header.parentElement !== headerParent) headerParent.insertBefore(header, headerParent.firstChild);
      if (caption.parentElement !== captionParent) {
        var afterHeader = header.nextSibling;
        if (afterHeader) headerParent.insertBefore(caption, afterHeader); else headerParent.appendChild(caption);
      }
    }
    function apply(){ window.innerWidth <= 576 ? toMobile() : toDesktop(); }
    apply();
    window.addEventListener('resize', apply);
  })();

  // ---- Comment edit toggles ----
  document.addEventListener('click', function (e) {
    var t = e.target;
    var edit = t.closest && t.closest('.edit-comment-link');
    if (edit) {
      e.preventDefault();
      var id = edit.getAttribute('data-comment-id');
      var form = document.querySelector('.edit-comment-form[data-comment-id="' + id + '"]');
      if (form) form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
      return;
    }
    var cancel = t.closest && t.closest('.cancel-edit');
    if (cancel) {
      e.preventDefault();
      var form = cancel.closest && cancel.closest('.edit-comment-form');
      if (form) form.style.display = 'none';
      return;
    }
  });

  // ---- Bootstrap carousel init & video pause ----
  var carouselEl = document.getElementById('postMediaCarousel');
  if (!carouselEl) return;

  // Pause helper
  function pauseAllVideos() {
    var vids = carouselEl.querySelectorAll('video');
    vids.forEach(function (v) { try { v.pause(); } catch (e) { } });
  }

  if (typeof bootstrap !== 'undefined' && bootstrap && bootstrap.Carousel) {
    var carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl, { interval: false, wrap: true });

    // Pause videos when sliding
    carouselEl.addEventListener('slide.bs.carousel', function () { pauseAllVideos(); });
    carouselEl.addEventListener('slid.bs.carousel', function () { pauseAllVideos(); });

    // Carousel persistence
    var key = 'carousel_index_' + postId;
    var storedIndex = localStorage.getItem(key);
    if (storedIndex !== null) {
      carousel.to(parseInt(storedIndex));
    }
    carouselEl.addEventListener('slid.bs.carousel', function (e) {
      var activeIndex = Array.from(carouselEl.querySelectorAll('.carousel-item')).indexOf(e.relatedTarget);
      localStorage.setItem(key, activeIndex);
    });

    // IMPORTANT: do NOT preventDefault on the control buttons; let Bootstrap handle the click
    // But ensure controls exist and are not overwritten by other handlers
    // (No extra click handlers here)
  } else {
    // Fallback manual navigation (if Bootstrap not present)
    var items = Array.prototype.slice.call(carouselEl.querySelectorAll('.carousel-item'));
    function showIndex(i) {
      if (!items.length) return;
      i = (i % items.length + items.length) % items.length;
      items.forEach(function (it, idx) {
        var active = idx === i;
        it.classList.toggle('active', active);
        it.style.display = active ? 'block' : 'none';
      });
      pauseAllVideos();
    }
    showIndex(items.findIndex(it => it.classList.contains('active')) || 0);
    var prevBtn = carouselEl.querySelector('.carousel-control-prev');
    var nextBtn = carouselEl.querySelector('.carousel-control-next');
    if (nextBtn) nextBtn.addEventListener('click', function (e) { e.preventDefault(); var cur = items.findIndex(it => it.classList.contains('active')); showIndex(cur + 1); });
    if (prevBtn) prevBtn.addEventListener('click', function (e) { e.preventDefault(); var cur = items.findIndex(it => it.classList.contains('active')); showIndex(cur - 1); });
  }
});


