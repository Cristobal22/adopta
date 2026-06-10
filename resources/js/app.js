import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';

// Soporte para subdirectorios locales en Apache
const baseDir = window.location.pathname.startsWith('/adopta/public') ? '/adopta/public' : '';

router.on('before', (event) => {
    const url = event.detail.visit.url;
    if (url.origin === window.location.origin && !url.pathname.startsWith(baseDir)) {
        url.pathname = baseDir + (url.pathname.startsWith('/') ? '' : '/') + url.pathname;
    }
});

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
