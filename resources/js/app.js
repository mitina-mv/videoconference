import './bootstrap';
import 'primevue/resources/themes/aura-light-green/theme.css'
import 'primeicons/primeicons.css'
import '../css/app.css';


import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const token = '2|ili21N0SHxEAblrDNqqG6onl5pkfZ0SE839l6cEO395b616c'

axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue)
            .use(ToastService)
            .mount(el);
    },
    progress: {
        color: '#188a42',
    },
});
