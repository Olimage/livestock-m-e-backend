import './bootstrap';
import '../assets/main.css'
import '../css/app.css'
import 'animate.css'

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js'
import { useToast } from '@/composables/useToast';


const appName = import.meta.env.VITE_APP_NAME || 'FMLD';



import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'

import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import $ from 'jquery';


 import { route } from 'ziggy-js'
   window.route = route

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Make toast available globally
        app.config.globalProperties.$toast = useToast();

        
        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Handle Inertia validation errors globally
import { router } from '@inertiajs/vue3';

router.on('error', (event) => {
    const toast = useToast();

    if (event.detail?.errors) {
        const errors = event.detail.errors;
        const firstError = Object.values(errors)[0];

        if (Array.isArray(firstError)) {
            toast.error(firstError[0]);
        } else {
            toast.error(firstError);
        }
    }
});

// Listen for flash messages from Laravel
router.on('success', (event) => {
    const toast = useToast();
    const page = event.detail.page;

    if (page.props.flash) {
        if (page.props.flash.success) toast.success(page.props.flash.success);
        if (page.props.flash.error) toast.error(page.props.flash.error);
        if (page.props.flash.warning) toast.warning(page.props.flash.warning);
        if (page.props.flash.info) toast.info(page.props.flash.info);
    }
});