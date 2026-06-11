import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-chat-realtime]')) {
        import('./chat-realtime').then(() => {
            window.dispatchEvent(new CustomEvent('chat-realtime:ready'));
        });
    }
});
