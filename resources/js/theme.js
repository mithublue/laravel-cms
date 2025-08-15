// Frontend theme bootstrap (no Inertia)
// Hotwired Turbo for SPA-like navigation
import * as Turbo from '@hotwired/turbo';
// Ensure Turbo Drive is started when using ESM import
try { Turbo.start(); } catch (_) { /* noop */ }

// Expose Turbo and ensure Drive is enabled
window.Turbo = Turbo;
if (Turbo?.setProgressBarDelay) {
  Turbo.setProgressBarDelay(100);
}

// Alpine.js for interactivity
import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Start Alpine for the initial page load
Alpine.start();

// Recycle Alpine components across Turbo visits
addEventListener('turbo:before-render', () => {
  try { Alpine.destroyTree(document.body); } catch (e) { /* noop */ }
});

function updateTitleFromMeta(root = document) {
  try {
    const meta = root.querySelector('meta[name="page-title"]');
    const appName = document.querySelector('meta[name="app-name"]')?.getAttribute('content') || document.title;
    if (meta) {
      const pageTitle = meta.getAttribute('content')?.trim();
      if (pageTitle) document.title = `${pageTitle} — ${appName}`;
    }
  } catch (_) { /* noop */ }
}

function annotateLinks(root = document) {
  try {
    const anchors = root.querySelectorAll('a[href]');
    for (const anchor of anchors) {
      if (anchor.dataset.turbo === 'false') continue;
      if (anchor.hasAttribute('download')) continue;
      if (anchor.getAttribute('target') === '_blank') continue;
      if (anchor.getAttribute('href')?.startsWith('#')) continue;
      // Skip if already inside the content frame
      if (anchor.closest('turbo-frame#content')) continue;
      // Only internal links
      const href = anchor.getAttribute('href');
      if (!href) continue;
      const url = new URL(href, window.location.origin);
      if (url.origin !== window.location.origin) continue;
      // If no explicit frame target, set to content
      if (!anchor.dataset.turboFrame) {
        anchor.setAttribute('data-turbo-frame', 'content');
      }
    }
  } catch (_) { /* noop */ }
}

addEventListener('turbo:load', () => {
  try { Alpine.initTree(document.body); } catch (e) { /* noop */ }
  updateTitleFromMeta(document);
  annotateLinks(document);
});

addEventListener('turbo:frame-load', (e) => {
  const frame = e.target;
  if (frame?.id === 'content') {
    // Re-init Alpine inside the updated frame
    try { Alpine.initTree(frame); } catch (e) { /* noop */ }
    updateTitleFromMeta(frame);
    annotateLinks(document);
  }
});

// Strong guarantee: route eligible internal link clicks into the content frame
document.addEventListener('click', (event) => {
  const anchor = event.target.closest('a');
  if (!anchor) return;
  const isModified = event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
  if (isModified) return;
  const href = anchor.getAttribute('href');
  if (!href || href.startsWith('#') || anchor.hasAttribute('download') || anchor.getAttribute('target') === '_blank') return;
  const url = new URL(href, window.location.origin);
  if (url.origin !== window.location.origin) return;
  // Ignore clicks within the content frame (Turbo will handle)
  if (anchor.closest('turbo-frame#content')) return;
  const frame = document.getElementById('content');
  if (!frame) return;
  // Prevent default navigation and drive the frame instead
  event.preventDefault();
  // Preserve path + query + hash; let Turbo Frames handle history via data-turbo-action="advance"
  frame.setAttribute('src', url.pathname + url.search + url.hash);
}, true);
