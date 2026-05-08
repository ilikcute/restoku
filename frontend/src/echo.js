import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echo = null;

try {
    const key = import.meta.env.VITE_REVERB_APP_KEY;
    const host = import.meta.env.VITE_REVERB_HOST;

    if (key && host) {
        echo = new Echo({
            broadcaster: 'reverb',
            key: key,
            wsHost: host,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        console.log('Laravel Echo initialized successfully');
    } else {
        console.warn('Laravel Echo skipped: Missing VITE_REVERB variables');
    }
} catch (error) {
    console.error('Failed to initialize Laravel Echo', error);
}

export default echo;
