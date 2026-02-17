<div id="confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <!-- Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" id="modal-icon">
                <!-- Icon will be set by JavaScript -->
            </div>

            <!-- Title -->
            <h3 class="text-lg font-medium text-gray-900 mb-2" id="modal-title">
                <!-- Title will be set by JavaScript -->
            </h3>

            <!-- Message -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modal-message">
                    <!-- Message will be set by JavaScript -->
                </p>
            </div>

            <!-- Input field for reason (optional) -->
            <div class="mt-4 px-7" id="reason-input-container" style="display: none;">
                <textarea
                    id="reason-input"
                    placeholder="Enter reason (optional)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    rows="3"
                ></textarea>
            </div>

            <!-- Buttons -->
            <div class="items-center px-4 py-3">
                <div class="flex space-x-3 justify-center">
                    <button
                        id="modal-cancel"
                        class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        id="modal-confirm"
                        class="px-4 py-2 text-white text-base font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                    >
                        <!-- Button text will be set by JavaScript -->
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class ConfirmationModal {
    constructor() {
        this.modal = document.getElementById('confirmation-modal');
        this.title = document.getElementById('modal-title');
        this.message = document.getElementById('modal-message');
        this.icon = document.getElementById('modal-icon');
        this.confirmBtn = document.getElementById('modal-confirm');
        this.cancelBtn = document.getElementById('modal-cancel');
        this.reasonInput = document.getElementById('reason-input');
        this.reasonContainer = document.getElementById('reason-input-container');

        this.setupEventListeners();
    }

    setupEventListeners() {
        this.cancelBtn.addEventListener('click', () => this.hide());

        // Close modal when clicking outside
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.hide();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                this.hide();
            }
        });
    }

    show(options = {}) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure you want to proceed?',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            type = 'warning', // warning, danger, success, info
            showReason = false,
            reasonPlaceholder = 'Enter reason (optional)',
            onConfirm = () => {},
            onCancel = () => {}
        } = options;

        this.title.textContent = title;
        this.message.textContent = message;
        this.confirmBtn.textContent = confirmText;
        this.cancelBtn.textContent = cancelText;

        // Set icon and colors based on type
        this.setType(type);

        // Show/hide reason input
        if (showReason) {
            this.reasonContainer.style.display = 'block';
            this.reasonInput.placeholder = reasonPlaceholder;
            this.reasonInput.value = '';
        } else {
            this.reasonContainer.style.display = 'none';
        }

        // Store callbacks
        this.onConfirm = onConfirm;
        this.onCancel = onCancel;

        // Remove existing event listeners
        this.confirmBtn.replaceWith(this.confirmBtn.cloneNode(true));
        this.confirmBtn = document.getElementById('modal-confirm');

        // Add new event listener
        this.confirmBtn.addEventListener('click', () => {
            const reason = showReason ? this.reasonInput.value.trim() : null;
            this.hide();
            this.onConfirm(reason);
        });

        this.modal.classList.remove('hidden');
    }

    hide() {
        this.modal.classList.add('hidden');
        if (this.onCancel) {
            this.onCancel();
        }
    }

    setType(type) {
        // Reset classes
        this.icon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4';
        this.confirmBtn.className = 'px-4 py-2 text-white text-base font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2';

        switch (type) {
            case 'danger':
                this.icon.innerHTML = `
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                `;
                this.icon.classList.add('bg-red-100');
                this.confirmBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
                break;
            case 'success':
                this.icon.innerHTML = `
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                `;
                this.icon.classList.add('bg-green-100');
                this.confirmBtn.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
                break;
            case 'info':
                this.icon.innerHTML = `
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                `;
                this.icon.classList.add('bg-blue-100');
                this.confirmBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
                break;
            default: // warning
                this.icon.innerHTML = `
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                `;
                this.icon.classList.add('bg-yellow-100');
                this.confirmBtn.classList.add('bg-yellow-600', 'hover:bg-yellow-700', 'focus:ring-yellow-500');
                break;
        }
    }
}

// Initialize the modal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing confirmation modal...');
    window.confirmationModal = new ConfirmationModal();
    console.log('Confirmation modal initialized:', window.confirmationModal);
});
</script>
