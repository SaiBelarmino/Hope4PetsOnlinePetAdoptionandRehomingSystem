// Landing page progressive enhancements (extended animations, parallax, counters)
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

  const show = (el) => {
    // Apply duration override via data attribute
    if (el.dataset.revealDur) {
      el.style.setProperty('--reveal-dur', el.dataset.revealDur);
    }
    el.classList.add('is-visible');
    // Trigger counter if present
    const ctr = el.matches('.counter') ? el : el.querySelector('.counter');
    if (ctr && !ctr.dataset.counted) {
      runCounter(ctr);
    }
  };
  const showAll = () => allReveal.forEach(show);

  if (!supportsIO) { showAll(); return; }

  if (allReveal.length) {
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
  }

  // 3) Counter up animation (supports data-target, optional data-suffix, data-duration)
  function runCounter(el) {
    const target = parseFloat(el.dataset.target || '0');
    const dur = parseInt(el.dataset.duration || '1600',10);
    const suffix = el.dataset.suffix || '';
    const start = performance.now();
    const startVal = 0;
    el.dataset.counted = '1';
    function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      const ease = p < 1 ? 1 - Math.pow(1 - p, 3) : 1; // cubic easeOut
      const val = Math.round(startVal + (target - startVal) * ease);
      el.textContent = val.toLocaleString() + suffix;
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  // 4) Parallax elements with data-parallax (value = factor, default 0.15)
  const parallaxEls = Array.from(document.querySelectorAll('[data-parallax]'));
  if (parallaxEls.length) {
    const onScroll = () => {
      const scrollY = window.scrollY || window.pageYOffset;
      parallaxEls.forEach(el => {
        const factor = parseFloat(el.dataset.parallax || '0.15');
        el.style.transform = `translateY(${scrollY * factor * -0.4}px)`; // move slower upward
      });
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // 5) Custom smooth scroll for in-page anchors / buttons with data-scroll-to
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const easeInOutCubic = (t) => t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
  function animateScrollTo(targetY, duration = 800) {
    const startY = window.scrollY || window.pageYOffset;
    const dist = targetY - startY;
    const startTime = performance.now();
    function step(now) {
      const p = Math.min(1, (now - startTime) / duration);
      const eased = easeInOutCubic(p);
      window.scrollTo(0, startY + dist * eased);
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  function handleTriggerClick(e) {
    const trigger = e.currentTarget;
    const href = trigger.getAttribute('href') || trigger.dataset.scrollTo;
    if (!href || href.charAt(0) !== '#') return;
    const el = document.querySelector(href);
    if (!el) return;
    e.preventDefault();
    const offset = parseInt(trigger.dataset.scrollOffset || el.dataset.scrollOffset || '70', 10);
    const top = el.getBoundingClientRect().top + window.pageYOffset - offset;
    if (prefersReduced) {
      window.scrollTo({ top, behavior: 'auto' });
    } else {
      animateScrollTo(top, parseInt(trigger.dataset.scrollDur || '850', 10));
    }
    // update hash without instant jump
    history.replaceState(null, '', href);
    // focus for accessibility after delay
    setTimeout(() => { if (el.tabIndex === -1) el.focus({ preventScroll: true }); }, 900);
  }
  const scrollTriggers = Array.from(document.querySelectorAll('a[href^="#"], [data-scroll-to]'))
    .filter(a => a.getAttribute('href') !== '#');
  scrollTriggers.forEach(a => a.addEventListener('click', handleTriggerClick));

  // 6) Assemble animation (split text into spans and stagger activate after reveal)
  const assembleTargets = Array.from(document.querySelectorAll('[data-assemble]'));
  function splitText(el) {
    if (el.dataset.assembled) return;
    const mode = el.dataset.assemble || 'words';
    const raw = el.textContent.trim();
    const frag = document.createDocumentFragment();
    function createSpan(txt, idx) {
      const span = document.createElement('span');
      span.className = 'assemble-item';
      span.textContent = txt;
      span.style.transitionDelay = `calc(var(--assemble-stagger) * ${idx})`;
      return span;
    }
    if (mode === 'chars') {
      Array.from(raw).forEach((ch, idx) => {
        if (ch === ' ') { frag.appendChild(document.createTextNode(' ')); return; }
        frag.appendChild(createSpan(ch, idx));
      });
    } else { // words (default)
      raw.split(/\s+/).forEach((w, idx) => {
        frag.appendChild(createSpan(w, idx));
        if (idx < raw.split(/\s+/).length - 1) frag.appendChild(document.createTextNode(' '));
      });
    }
    el.setAttribute('aria-label', raw); // accessibility
    el.textContent = '';
    el.appendChild(frag);
    el.dataset.assembled = '1';
  }
  assembleTargets.forEach(splitText);

  // If already visible (above fold), mark assembled instantly after a frame
  requestAnimationFrame(() => {
    assembleTargets.forEach(el => {
      if (el.closest('.reveal')?.classList.contains('is-visible')) {
        requestAnimationFrame(() => el.classList.add('is-assembled'));
      }
    });
  });

  // Observe reveals to trigger assembled state
  const assembleObs = new IntersectionObserver((entries) => {
    entries.forEach(ent => {
      if (ent.isIntersecting) {
        const host = ent.target;
        // Delay slightly to sync with reveal
        const delay = parseInt(host.dataset.assembleDelay || '120', 10);
        setTimeout(() => host.classList.add('is-assembled'), delay);
        assembleObs.unobserve(host);
      }
    });
  }, { threshold: 0.2 });
  assembleTargets.forEach(el => assembleObs.observe(el));
})();
