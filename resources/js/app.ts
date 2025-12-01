import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { initializeFontPreferences } from './composables/useFontPreferences';
import { initializeLocale } from './composables/useLocale';
import { i18n } from './i18n';
import { offlineDB } from './lib/offlineDB';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize IndexedDB for offline storage
offlineDB.init().catch((error) => {
    console.error('[App] Failed to initialize offline database:', error);
});

// Register service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js', { scope: '/' })
            .then((registration) => {
                console.log('[App] Service Worker registered:', registration.scope);
            })
            .catch((error) => {
                console.error('[App] Service Worker registration failed:', error);
            });
    });
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n);

        app.mount(el);

        // Initialize font preferences after mount to ensure page props are available
        initializeFontPreferences();

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
initializeLocale(i18n);
