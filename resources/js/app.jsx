import '../sass/app.scss';
import './bootstrap';
import './bootstrap_theme';
import $ from 'jquery';
import select2 from 'select2';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import route from './route';

window.$ = window.jQuery = $;
select2();
window.route = route;

createInertiaApp({
    title: (title) => `${title} - Vrijdag voetbal`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: { color: '#4B5563' },
});
