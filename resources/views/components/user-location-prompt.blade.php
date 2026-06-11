@if(session('request_geo_location'))
<div id="session-location-modal" class="fixed inset-0 z-[85] overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Improve map accuracy</h3>
            <p class="mt-2 text-sm text-gray-600">
                Share your current location so administrators can see your position more accurately on the user map.
            </p>
            <p id="session-location-status" class="mt-3 hidden text-sm text-amber-700"></p>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" id="session-location-skip-btn"
                        class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Not now
                </button>
                <button type="button" id="session-location-allow-btn"
                        class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    Share location
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('session-location-modal');
    const allowBtn = document.getElementById('session-location-allow-btn');
    const skipBtn = document.getElementById('session-location-skip-btn');
    const status = document.getElementById('session-location-status');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function closeModal() {
        modal?.remove();
    }

    skipBtn?.addEventListener('click', closeModal);

    allowBtn?.addEventListener('click', function () {
        if (!navigator.geolocation) {
            if (status) {
                status.textContent = 'Location is not supported by this browser.';
                status.classList.remove('hidden');
            }
            return;
        }

        allowBtn.disabled = true;
        skipBtn.disabled = true;
        if (status) {
            status.textContent = 'Getting your location...';
            status.classList.remove('hidden');
        }

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                try {
                    await fetch(@json(route('user.location.store')), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy,
                            context: 'session',
                        }),
                    });
                } catch (error) {
                    console.error(error);
                } finally {
                    closeModal();
                }
            },
            (error) => {
                if (status) {
                    status.textContent = error.code === error.PERMISSION_DENIED
                        ? 'Location permission was denied.'
                        : 'Unable to get your location right now.';
                    status.classList.remove('hidden');
                }
                allowBtn.disabled = false;
                skipBtn.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 12000,
                maximumAge: 0,
            }
        );
    });
});
</script>
@endif
