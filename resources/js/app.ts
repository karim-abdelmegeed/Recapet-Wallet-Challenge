import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const getCookie = (name: string): string | null => {
    if (typeof document === 'undefined') {
        return null;
    }

    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length < 2) {
        return null;
    }

    return parts.pop()?.split(';').shift() || null;
};

const getXsrfToken = (): string | null => {
    const raw = getCookie('XSRF-TOKEN');
    if (!raw) {
        return null;
    }

    try {
        return decodeURIComponent(raw);
    } catch {
        return raw;
    }
};

const isSameOrigin = (input: RequestInfo | URL): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    const urlString =
        typeof input === 'string'
            ? input
            : input instanceof URL
                ? input.toString()
                : input.url;

    if (urlString.startsWith('/')) {
        return true;
    }

    try {
        const url = new URL(urlString, window.location.origin);
        return url.origin === window.location.origin;
    } catch {
        return false;
    }
};

const attachGlobalCsrfToFetch = (): void => {
    if (typeof window === 'undefined' || !('fetch' in window)) {
        return;
    }

    const originalFetch = window.fetch.bind(window);

    window.fetch = async (input: RequestInfo | URL, init?: RequestInit) => {
        const method = (init?.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
        const shouldAttachCsrf = isSameOrigin(input) && !['GET', 'HEAD', 'OPTIONS'].includes(method);

        if (!shouldAttachCsrf) {
            return originalFetch(input, init);
        }

        const xsrf = getXsrfToken();
        if (!xsrf) {
            return originalFetch(input, init);
        }

        const headers = new Headers(init?.headers || (input instanceof Request ? input.headers : undefined));

        if (!headers.has('X-XSRF-TOKEN')) {
            headers.set('X-XSRF-TOKEN', xsrf);
        }

        if (!headers.has('X-Requested-With')) {
            headers.set('X-Requested-With', 'XMLHttpRequest');
        }

        return originalFetch(input, {
            ...(init || {}),
            headers,
        });
    };
};

attachGlobalCsrfToFetch();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
