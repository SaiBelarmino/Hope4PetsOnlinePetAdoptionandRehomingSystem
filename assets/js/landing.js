// Landing page progressive enhancements
(() => {
  // 1) Body fade-in to avoid flash of unanimated content
  document.documentElement.classList.remove('no-js');
  document.body.classList.add('page-preload');
  window.requestAnimationFrame(() => {
    // Give the browser a tick to apply class before transitioning
    setTimeout(() => {
      document.body.classList.add('is-ready');
      // remove preload flag after transition for cleanliness
      setTimeout(() => document.body.classList.remove('page-preload'), 500);
    }, 10);
  });

  // 2) Scroll reveal for elements with .reveal*
  const supportsIO = 'IntersectionObserver' in window;
  const allReveal = Array.from(document.querySelectorAll('.reveal'));
  if (!allReveal.length) return;

  // apply delay from data attribute or from delay-X classes
  const mapClassDelay = (el) => {
    if (el.hasAttribute('data-reveal-delay')) {
      el.style.setProperty('--reveal-delay', el.getAttribute('data-reveal-delay'));
      return;
    }
    for (let i = 1; i <= 8; i++) {
      if (el.classList.contains(`delay-${i}`)) {
        el.style.setProperty('--reveal-delay', `${i * 100}ms`);
        break;
      }
    }
  };
  allReveal.forEach(mapClassDelay);

  const show = (el) => el.classList.add('is-visible');
  const showAll = () => allReveal.forEach(show);

  if (!supportsIO) { showAll(); return; }

  // Within shared containers, apply slight stagger if not specified
  const containers = new Map();
  allReveal.forEach((el) => {
    const parent = el.closest('[data-reveal-group]') || el.parentElement;
    if (!containers.has(parent)) containers.set(parent, []);
    containers.get(parent).push(el);
  });

  containers.forEach((els) => {
    // only add implicit stagger if none of the group have explicit delay
    const hasExplicit = els.some((el) => el.hasAttribute('data-reveal-delay') || /delay-\d/.test(el.className));
    if (!hasExplicit) {
      els.forEach((el, idx) => el.style.setProperty('--reveal-delay', `${Math.min(idx * 80, 480)}ms`));
    }
  });

  const io = new IntersectionObserver((entries) => {
    for (const e of entries) {
      if (e.isIntersecting) {
        show(e.target);
        io.unobserve(e.target);
      }
    }
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  allReveal.forEach((el) => io.observe(el));
})();
