// Frontend theme bootstrap (no Inertia)
// Hotwired Turbo for SPA-like navigation
import '@hotwired/turbo';

// Alpine.js for interactivity
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Optional: expose Turbo session if needed
// window.Turbo.session.drive = true;
