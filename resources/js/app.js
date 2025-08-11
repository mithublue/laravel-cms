import './bootstrap';
import './elements/turbo-echo-stream-tag';
import './libs';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import '../css/app.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ),
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) });
    vueApp.use(plugin);
    // Register Ziggy's route() globally for Vue templates
    vueApp.use(ZiggyVue, typeof window !== 'undefined' ? window.Ziggy : undefined);
    vueApp.mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});