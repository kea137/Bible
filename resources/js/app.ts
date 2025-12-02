import '../css/app.css';
import 'vue-sonner/style.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeAnalytics } from './composables/useAnalytics';
import { initializeTheme } from './composables/useAppearance';
import { initializeFeatureFlags } from './composables/useFeatureFlags';
import { initializeFontPreferences } from './composables/useFontPreferences';
import { initializeLocale } from './composables/useLocale';
import { i18n } from './i18n';
import { offlineDB } from './lib/offlineDB';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize IndexedDB for offline storage
offlineDB.init().catch((error) => {
    console.error('[App] Failed to initialize offline database:', error);
});

// Note: Service worker registration is handled in useOffline composable
// to ensure proper lifecycle management and avoid duplicate registrations

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

        // Initialize locale from server-provided props (DB source of truth)
        initializeLocale(i18n);

        // Initialize font preferences after mount to ensure page props are available
        initializeFontPreferences();

        // Initialize theme after mount to ensure Inertia page props exist
        initializeTheme();

        // Initialize feature flags
        initializeFeatureFlags();

        // Initialize analytics
        initializeAnalytics();

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
