import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     wsHost: import.meta.env.VITE_REVERB_HOST,
//     wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
//     wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

// Prefer non-TLS websocket locally. Use wss only when the page is served
// via HTTPS or VITE_REVERB_SCHEME is set to 'https'. This prevents
// attempts to connect to wss://localhost when the socket server is
// listening on plain ws://localhost:8080.
const envHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const envPort = import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : undefined;
const envScheme = import.meta.env.VITE_REVERB_SCHEME || (window.location.protocol === 'https:' ? 'https' : 'http');

const useTLS = envScheme === 'https' || window.location.protocol === 'https:';
const wsPort = envPort ?? (useTLS ? 443 : 8080);

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: envHost,
    wsPort: wsPort,
    wssPort: wsPort,
    forceTLS: !!useTLS,
    // prefer the transport appropriate for the environment
    enabledTransports: useTLS ? ['wss', 'ws'] : ['ws'],
    // enable withCredentials so cookie-based session auth works for private channels
    auth: {
        withCredentials: true
    }
});
