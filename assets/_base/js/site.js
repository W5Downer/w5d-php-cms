/* W5D site script. No dependencies, no network requests.
 * 1. Sticky CTA: shown on mobile after 40% of the page has been scrolled.
 */
(function () {
  'use strict';
  var bar = document.querySelector('[data-sticky]');
  if (!bar) { return; }
  function update() {
    var root = document.documentElement;
    var max = (root.scrollHeight - root.clientHeight) || 1;
    var ratio = (window.scrollY || root.scrollTop || 0) / max;
    bar.hidden = ratio < 0.4;
  }
  window.addEventListener('scroll', update, { passive: true });
  update();
}());
