// postview.js - clean carousel initialization and UI handlers
document.addEventListener('DOMContentLoaded', function () {
  // ---- Caption See more / See less ----
  var captionText = document.getElementById('captionText');
  var seeMoreBtn = document.getElementById('seeMoreBtn');
  var fadeOverlay = document.getElementById('fadeOverlay');
  var COLLAPSED_HEIGHT = 120;

  if (captionText) {
    captionText.style.maxHeight = COLLAPSED_HEIGHT + 'px';
    captionText.style.overflow = 'hidden';
    if (captionText.scrollHeight <= COLLAPSED_HEIGHT + 2) {
      if (seeMoreBtn) seeMoreBtn.style.display = 'none';
      if (fadeOverlay) fadeOverlay.style.display = 'none';
    }
  }

  if (seeMoreBtn) {
    seeMoreBtn.addEventListener('click', function (e) {
      e.preventDefault();
      if (!captionText) return;
      var isCollapsed = captionText.style.maxHeight && captionText.style.maxHeight !== 'none';
      if (isCollapsed) {
        captionText.style.maxHeight = 'none';
        if (fadeOverlay) fadeOverlay.style.display = 'none';
        seeMoreBtn.textContent = 'See less';
      } else {
        captionText.style.maxHeight = COLLAPSED_HEIGHT + 'px';
        if (fadeOverlay) fadeOverlay.style.display = 'block';
        seeMoreBtn.textContent = 'See more';
      }
    });
  }

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
