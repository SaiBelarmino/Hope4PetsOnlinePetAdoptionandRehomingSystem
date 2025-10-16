// Caption toggle for PostView.js
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('seeMoreBtn');
  const caption = document.getElementById('captionText');
  const fade = document.getElementById('fadeOverlay');

  const COLLAPSED_HEIGHT = 80; // px
  let expanded = false;
  let initialized = false;

  if (!caption) return;

  function toggle() {
    if (!caption) return;

    if (!expanded) {
      // expand
      caption.style.maxHeight = caption.scrollHeight + 'px';
      if (fade) fade.style.display = 'none';
      if (btn) btn.textContent = 'See less';
      setTimeout(() => {
        caption.style.maxHeight = 'none';
      }, 350);
    } else {
      // collapse
      // ensure we have a numeric maxHeight to transition from
      caption.style.maxHeight = caption.scrollHeight + 'px';
      // force reflow
      /* eslint-disable no-unused-expressions */
      caption.offsetHeight;
      /* eslint-enable no-unused-expressions */
      caption.style.maxHeight = COLLAPSED_HEIGHT + 'px';
      if (fade) fade.style.display = 'block';
      if (btn) btn.textContent = 'See more';
    }

    expanded = !expanded;
  }

  function init() {
    if (initialized) return;
    initialized = true;

    // If content fits within collapsed height, hide the button and overlay
    if (caption.scrollHeight <= COLLAPSED_HEIGHT) {
      if (btn) btn.style.display = 'none';
      if (fade) fade.style.display = 'none';
      caption.style.maxHeight = 'none';
      return;
    }

    // Ensure initial collapsed state
    caption.style.maxHeight = COLLAPSED_HEIGHT + 'px';
    if (btn) btn.textContent = 'See more';
    if (fade) fade.style.display = 'block';

    if (btn) btn.addEventListener('click', toggle);

    // Recalculate on window resize to adjust to new widths/heights
    window.addEventListener('resize', () => {
      // If expanded, keep expanded; otherwise ensure collapsed max-height is correct
      if (!expanded) {
        caption.style.maxHeight = COLLAPSED_HEIGHT + 'px';
      }
    });
  }

  // Run init on next frame so measurements are accurate (images/fonts loaded)
  requestAnimationFrame(init);
});