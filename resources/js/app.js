import './bootstrap';
import '../css/app.css';
import 'primevue/resources/themes/aura-light-indigo/theme.css'
import 'primeicons/primeicons.css'

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const token = import.meta.env.TOKEN_APP

axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faHand } from '@fortawesome/free-regular-svg-icons'

/* add icons to the library */
library.add(faHand)

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('font-awesome-icon', FontAwesomeIcon)
            .use(PrimeVue)
            .use(ToastService)
            .mount(el);
    },
    progress: {
        color: 'var(--primary-color)',
    },
});
