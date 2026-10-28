<div id="travel-time-agreement-modal"
     class="user-portal-modal user-portal-modal--page hidden"
     aria-hidden="true"
     role="dialog"
     aria-labelledby="travel-time-agreement-title">
    <div class="user-portal-modal__backdrop" data-travel-agreement-dismiss></div>
    <div class="user-portal-modal__dialog">
        <div class="user-portal-modal__panel">
            <div class="user-portal-modal__header">
                <h2 id="travel-time-agreement-title" class="user-portal-modal__title">Travel time overtime</h2>
                <p class="user-portal-modal__subtitle">Review net hours and confirm before submitting.</p>
            </div>

            <div class="user-portal-modal__body">
                <div class="rounded-lg border border-indigo-100 bg-indigo-50/60 px-4 py-3 space-y-2 text-sm text-gray-800">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-600">Travel location</span>
                        <span id="travel-agreement-location" class="font-semibold text-right">—</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-600">Travel time</span>
                        <span id="travel-agreement-travel-hours" class="font-mono font-semibold">—</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-600">Total hours reported</span>
                        <span id="travel-agreement-gross-hours" class="font-mono font-semibold">—</span>
                    </div>
                    <div class="border-t border-indigo-100 pt-2 flex justify-between gap-3">
                        <span class="text-gray-700 font-medium">Net overtime (compensable)</span>
                        <span id="travel-agreement-net-hours" class="font-mono font-bold text-indigo-700">—</span>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 leading-relaxed max-h-32 overflow-y-auto custom-scrollbar">
                    <p class="font-semibold mb-1">Terms</p>
                    <p>
                        Food and accommodation are provided during travel. Overtime pay applies to <strong>net overtime hours</strong> after travel time is deducted.
                    </p>
                </div>

                <label class="mt-4 flex items-start gap-3 cursor-pointer">
                    <input type="checkbox"
                           id="travel-time-agreement-checkbox"
                           class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">
                        I agree to the travel time overtime terms above.
                    </span>
                </label>
                @error('travel_time_terms_agreed')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="user-portal-modal__footer">
                <button type="button"
                        data-travel-agreement-dismiss
                        class="user-portal-modal__btn user-portal-modal__btn--secondary">
                    Cancel
                </button>
                <button type="button"
                        id="travel-time-agreement-confirm-btn"
                        disabled
                        class="user-portal-modal__btn user-portal-modal__btn--primary min-w-[9.5rem]">
                    <svg id="travel-agreement-confirm-spinner"
                         class="hidden animate-spin h-4 w-4"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="travel-agreement-confirm-text">Agree and submit</span>
                </button>
            </div>
        </div>
    </div>
</div>
