import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
 
 // Alpine.js for public Blade + Turbo pages and Inertia components that use Alpine
 import Alpine from 'alpinejs';
 window.Alpine = Alpine;
 Alpine.start();

 // Note: Turbo is initialized for the public theme in resources/js/theme.js
