import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echoInstance = null;
let presenceChannel = null;
let activeChannel = null;
let activeChannelKey = null;
let callbacks = {};
let currentUserId = null;
let typingHideTimer = null;
let lastTypingSentAt = 0;
const TYPING_THROTTLE_MS = 1200;

function buildEcho() {
    if (echoInstance) {
        return echoInstance;
    }

    const key = import.meta.env.VITE_REVERB_APP_KEY;
    if (!key) {
        console.warn('Chat realtime disabled: missing VITE_REVERB_APP_KEY.');
        return null;
    }

    echoInstance = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    return echoInstance;
}

function directChannelName(userIdA, userIdB) {
    const ids = [Number(userIdA), Number(userIdB)].sort((a, b) => a - b);
    return `chat.direct.${ids[0]}.${ids[1]}`;
}

function leaveActiveChannel() {
    if (!echoInstance || !activeChannel || !activeChannelKey) {
        activeChannel = null;
        activeChannelKey = null;
        return;
    }

    echoInstance.leave(activeChannelKey);
    activeChannel = null;
    activeChannelKey = null;
}

function bindMessageChannel(channel, channelKey) {
    channel.listen('.message.sent', (payload) => {
        if (typeof callbacks.onMessage === 'function') {
            callbacks.onMessage(payload);
        }
    });

    channel.listen('.messages.read', (payload) => {
        if (typeof callbacks.onMessagesRead === 'function') {
            callbacks.onMessagesRead(payload);
        }
    });

    channel.listenForWhisper('typing', (payload) => {
        if (Number(payload?.user_id) === Number(currentUserId)) {
            return;
        }

        if (typeof callbacks.onTyping === 'function') {
            callbacks.onTyping(payload);
        }

        if (typingHideTimer) {
            clearTimeout(typingHideTimer);
        }

        typingHideTimer = setTimeout(() => {
            if (typeof callbacks.onTypingStop === 'function') {
                callbacks.onTypingStop();
            }
        }, 2500);
    });

    activeChannel = channel;
    activeChannelKey = channelKey;
}

function joinPresence() {
    const echo = buildEcho();
    if (!echo || presenceChannel) {
        return;
    }

    presenceChannel = echo.join('chat.presence');

    presenceChannel.here((users) => {
        if (typeof callbacks.onPresenceSync === 'function') {
            callbacks.onPresenceSync(users);
        }
    });

    presenceChannel.joining((user) => {
        if (typeof callbacks.onPresenceJoin === 'function') {
            callbacks.onPresenceJoin(user);
        }
    });

    presenceChannel.leaving((user) => {
        if (typeof callbacks.onPresenceLeave === 'function') {
            callbacks.onPresenceLeave(user);
        }
    });
}

window.ChatRealtime = {
    init(config) {
        currentUserId = Number(config.userId);
        callbacks = {
            onMessage: config.onMessage,
            onTyping: config.onTyping,
            onTypingStop: config.onTypingStop,
            onMessagesRead: config.onMessagesRead,
            onPresenceSync: config.onPresenceSync,
            onPresenceJoin: config.onPresenceJoin,
            onPresenceLeave: config.onPresenceLeave,
        };

        const echo = buildEcho();
        if (!echo) {
            return false;
        }

        joinPresence();
        return true;
    },

    switchChannel(chatType, targetId, peerId = null) {
        const echo = buildEcho();
        if (!echo) {
            return;
        }

        leaveActiveChannel();

        let channelName = null;
        if (chatType === 'friend' && targetId) {
            channelName = directChannelName(currentUserId, targetId);
        } else if (chatType === 'group' && targetId) {
            channelName = `chat.group.${targetId}`;
        } else if (chatType === 'anonymous' && targetId) {
            channelName = `chat.anonymous.${targetId}`;
        }

        if (!channelName) {
            return;
        }

        bindMessageChannel(echo.private(channelName), channelName);
    },

    leaveChannel() {
        leaveActiveChannel();
    },

    sendTypingSignal(displayName) {
        if (!activeChannel) {
            return;
        }

        const now = Date.now();
        if (now - lastTypingSentAt < TYPING_THROTTLE_MS) {
            return;
        }

        lastTypingSentAt = now;
        activeChannel.whisper('typing', {
            user_id: currentUserId,
            name: displayName || 'Someone',
        });
    },
};

export default window.ChatRealtime;
