// Frontend theme bootstrap (no Inertia)
// Hotwired Turbo for SPA-like navigation
import * as Turbo from '@hotwired/turbo';

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
  // Tear down Alpine on the outgoing DOM to avoid duplicate inits
  try { Alpine.destroyTree(document.body); } catch (e) { /* noop */ }
});

addEventListener('turbo:load', () => {
  // Initialize Alpine on the new DOM after Turbo renders
  try { Alpine.initTree(document.body); } catch (e) { /* noop */ }
});
