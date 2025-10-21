<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
    <!-- Toast notifications will be dynamically inserted here -->
</div>

<script>
// Toast Notification System
class ToastNotification {
    static show(message, type = 'info', duration = 5000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const toastId = 'toast-' + Date.now();

        // Toast types and their styles
        const types = {
            success: {
                bg: 'bg-green-500',
                text: 'text-white',
                icon: '✓',
                border: 'border-green-600'
            },
            error: {
                bg: 'bg-red-500',
                text: 'text-white',
                icon: '✕',
                border: 'border-red-600'
            },
            warning: {
                bg: 'bg-yellow-500',
                text: 'text-white',
                icon: '⚠',
                border: 'border-yellow-600'
            },
            info: {
                bg: 'bg-blue-500',
                text: 'text-white',
                icon: 'ℹ',
                border: 'border-blue-600'
            }
        };

        const toastType = types[type] || types.info;

        toast.id = toastId;
        toast.className = `transform transition-all duration-300 ease-in-out translate-x-full opacity-0 ${toastType.bg} ${toastType.text} ${toastType.border} border rounded-lg shadow-lg p-4 max-w-sm w-full flex items-center space-x-3`;

        toast.innerHTML = `
            <div class="flex-shrink-0">
                <span class="text-lg">${toastType.icon}</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <div class="flex-shrink-0">
                <button onclick="ToastNotification.hide('${toastId}')" class="text-white hover:text-gray-200 focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        }, 100);

        // Auto hide after duration
        if (duration > 0) {
            setTimeout(() => {
                this.hide(toastId);
            }, duration);
        }

        return toastId;
    }

    static hide(toastId) {
        const toast = document.getElementById(toastId);
        if (!toast) return;

        // Animate out
        toast.classList.add('translate-x-full', 'opacity-0');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    static success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    static error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    static warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    static info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
}

// Make ToastNotification globally available
window.ToastNotification = ToastNotification;
</script>
