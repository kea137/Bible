import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { initializeLocale } from './composables/useLocale';
import { i18n } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
            const vueApp = createApp({ render: () => h(App, props) });
            // Import and use VueInstantSearch for Vue 3
            // @ts-ignore
            import('vue-instantsearch/vue3/es').then(({ default: VueInstantSearch }) => {
                vueApp.use(VueInstantSearch);
                vueApp
                    .use(plugin)
                    .use(i18n)
                    .mount(el);
            });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
initializeLocale(i18n);
