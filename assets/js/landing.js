// Scroll reveal for elements with .reveal
(() => {
  const supportsIO = 'IntersectionObserver' in window;
  const els = Array.from(document.querySelectorAll('.reveal'));
  if (!els.length) return;

  const show = el => el.classList.add('is-visible');
  const showAll = () => els.forEach(show);

  if (!supportsIO) { showAll(); return; }

  const io = new IntersectionObserver((entries) => {
    for (const e of entries) {
      if (e.isIntersecting) {
        show(e.target);
        io.unobserve(e.target);
      }
    }
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  els.forEach(el => io.observe(el));
})();
