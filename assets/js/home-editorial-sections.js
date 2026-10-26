(() => {
  'use strict';

  if (!window.gsap || !window.ScrollTrigger || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  gsap.registerPlugin(ScrollTrigger);

  const reveal = (targets, trigger, options = {}) => {
    const nodes = gsap.utils.toArray(targets);
    if (!nodes.length) return;
    gsap.from(nodes, {
      y: options.y ?? 44,
      opacity: 0,
      duration: options.duration ?? 0.9,
      stagger: options.stagger ?? 0.1,
      ease: 'power3.out',
      scrollTrigger: { trigger: trigger || nodes[0], start: options.start || 'top 84%', once: true }
    });
  };

  reveal('.ci-ed-section-head > *', '.ci-ed-standards');
  reveal('.ci-ed-standard-grid article', '.ci-ed-standard-grid', { y: 58, stagger: 0.14 });
  reveal('.ci-ed-living-head > *', '.ci-ed-living-head');
  reveal('.ci-ed-living-card', '.ci-ed-living-grid', { y: 70, stagger: 0.13, duration: 1 });
  reveal('.ci-ed-maker-copy > *', '.ci-ed-maker-copy', { y: 48, stagger: 0.11 });
  reveal('.ci-ed-journey-head > *', '.ci-ed-journey-head');
  reveal('.ci-ed-journey-steps article', '.ci-ed-journey-steps', { y: 52, stagger: 0.14 });
  reveal('.ci-ed-words-title > *, .ci-ed-words blockquote', '.ci-ed-words', { y: 44, stagger: 0.12 });
  reveal('.ci-ed-tomorrow-content > *', '.ci-ed-tomorrow', { y: 42, stagger: 0.12, start: 'top 75%' });

  gsap.fromTo('.ci-ed-maker-image img', { scale: 1.1, yPercent: -3 }, {
    scale: 1.03,
    yPercent: 4,
    ease: 'none',
    scrollTrigger: { trigger: '.ci-ed-maker', start: 'top bottom', end: 'bottom top', scrub: 1 }
  });

  gsap.fromTo('.ci-ed-tomorrow > img', { scale: 1.08, yPercent: -3 }, {
    scale: 1.01,
    yPercent: 4,
    ease: 'none',
    scrollTrigger: { trigger: '.ci-ed-tomorrow', start: 'top bottom', end: 'bottom top', scrub: 1 }
  });

  window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
})();
