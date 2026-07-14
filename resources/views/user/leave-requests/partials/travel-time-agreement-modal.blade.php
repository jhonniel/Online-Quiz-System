<div id="travel-time-agreement-modal"
     class="hidden fixed inset-0 z-[60]"
     aria-hidden="true"
     role="dialog"
     aria-labelledby="travel-time-agreement-title">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" data-travel-agreement-dismiss></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl border border-gray-200 pointer-events-auto">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 id="travel-time-agreement-title" class="text-base font-semibold text-gray-900">Travel Time Overtime Agreement</h2>
                <p class="mt-1 text-sm text-gray-500">Review the net overtime calculation and confirm before submitting.</p>
            </div>

            <div class="px-5 py-4 space-y-4">
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

                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 leading-relaxed">
                    <p class="font-semibold mb-1">Terms</p>
                    <p>
                        The company provides <strong>Food</strong> and <strong>Accommodation</strong> during travel.
                        Overtime compensation applies only to your <strong>net overtime hours</strong> after travel time is deducted.
                        Travel time is not compensated as overtime because allocation are provided for that period.
                    </p>
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox"
                           id="travel-time-agreement-checkbox"
                           class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">
                        I have read and agree to the travel time overtime terms above.
                    </span>
                </label>
                @error('travel_time_terms_agreed')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex flex-wrap items-center justify-end gap-2">
                <button type="button"
                        data-travel-agreement-dismiss
                        class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button"
                        id="travel-time-agreement-confirm-btn"
                        disabled
                        class="inline-flex items-center justify-center min-w-[9.5rem] px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg id="travel-agreement-confirm-spinner"
                         class="hidden animate-spin h-4 w-4 mr-2"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="travel-agreement-confirm-text">Agree and Submit</span>
                </button>
            </div>
        </div>
    </div>
</div>
