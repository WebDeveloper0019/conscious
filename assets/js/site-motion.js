(() => {
  'use strict';

  const header = document.querySelector('.bc-header');
  if (!header) return;
  document.body.classList.add('ci-motion-site');

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const oldHomepage = document.querySelector('.ci-old-home');
  if (oldHomepage) oldHomepage.setAttribute('hidden', '');

  const syncHeader = () => document.body.classList.toggle('ci-page-scrolled', window.scrollY > 18);
  syncHeader();
  window.addEventListener('scroll', syncHeader, { passive: true });

  if (reduceMotion || !('IntersectionObserver' in window)) return;

  const revealObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('ci-motion-in');
      observer.unobserve(entry.target);
    });
  }, { rootMargin: '0px 0px -9% 0px', threshold: 0.08 });

  const registerReveal = (element, variant = '') => {
    if (!element || element.closest('[hidden], .ci-old-home, .ci-home')) return;
    element.classList.add('ci-motion-reveal');
    if (variant) element.classList.add(variant);
    revealObserver.observe(element);
  };

  document.querySelectorAll('main section:not([hidden])').forEach(section => {
    const headingBlock = section.querySelector(':scope > .container > .bc-section-header, :scope > .container > .bc-section-label, :scope > .container > h1, :scope > .container > h2, :scope > .bc-section-header');
    registerReveal(headingBlock);

    const grids = section.querySelectorAll('[class*="grid"], .row');
    grids.forEach(grid => {
      const children = Array.from(grid.children).filter(child => child.getBoundingClientRect().height > 32).slice(0, 12);
      children.forEach((child, index) => {
        registerReveal(child, 'ci-motion-scale');
        child.style.transitionDelay = `${Math.min(index, 5) * 70}ms`;
        if (child.matches('article, [class*="card"], [class*="tile"]') || child.querySelector(':scope > [class*="card"]')) child.classList.add('ci-motion-hover');
      });
    });
  });

  document.querySelectorAll('main figure, main [class*="image-wrap"], main [class*="image-panel"]').forEach((figure, index) => {
    if (index > 30 || figure.closest('[hidden], .ci-old-home, .ci-home') || !figure.querySelector('img')) return;
    figure.classList.add('ci-motion-image');
    revealObserver.observe(figure);
  });

  const enhanceDynamicContent = root => {
    const cards = [];
    if (root.nodeType === 1 && root.matches('[class*="card"]')) cards.push(root);
    if (root.querySelectorAll) cards.push(...root.querySelectorAll('[class*="card"]'));
    cards.forEach((card, index) => {
      if (card.closest('[hidden], .ci-old-home, .ci-home') || card.classList.contains('ci-motion-hover')) return;
      card.classList.add('ci-motion-hover');
      registerReveal(card, 'ci-motion-scale');
      card.style.transitionDelay = `${Math.min(index, 5) * 55}ms`;
    });
  };

  enhanceDynamicContent(document);
  const main = document.querySelector('main');
  if (main) {
    let enhancementFrame = 0;
    const dynamicObserver = new MutationObserver(mutations => {
      const added = mutations.flatMap(mutation => Array.from(mutation.addedNodes)).filter(node => node.nodeType === 1);
      if (!added.length) return;
      cancelAnimationFrame(enhancementFrame);
      enhancementFrame = requestAnimationFrame(() => added.forEach(enhanceDynamicContent));
    });
    dynamicObserver.observe(main, { childList: true, subtree: true });
  }

  document.querySelectorAll('.bc-newsletter-inner, .bc-footer-grid > *, .bc-footer-bottom-inner').forEach((element, index) => {
    registerReveal(element, index % 2 ? 'ci-motion-right' : 'ci-motion-left');
  });
})();
