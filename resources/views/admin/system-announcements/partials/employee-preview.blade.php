<div class="hidden xl:block">
    <div class="sticky top-6 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Employee popup preview</h3>
            <span class="text-[10px] font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">Live</span>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white shadow-lg overflow-hidden ring-1 ring-black/5">
            <div class="border-b border-gray-100 px-5 py-4 bg-gradient-to-b from-gray-50 to-white">
                <p class="text-base font-bold text-gray-900 text-center" x-text="title || 'Announcement title'"></p>
                <p class="mt-1 text-xs text-gray-500 text-center">Please read this announcement.</p>
            </div>
            <div class="px-5 py-4 text-sm text-gray-700 leading-relaxed max-h-[320px] overflow-y-auto custom-scrollbar employee-announcement-content" x-html="formatPreview(content)"></div>
            <div class="border-t border-gray-100 px-5 py-4 bg-gray-50 space-y-3">
                <div class="flex items-start gap-3 opacity-60">
                    <div class="mt-0.5 w-4 h-4 rounded border border-gray-300 bg-white"></div>
                    <span class="text-sm text-gray-600">I have read and agree to this announcement.</span>
                </div>
                <div class="w-full rounded-lg bg-indigo-600/80 text-center py-2.5 text-sm font-semibold text-white">I agree</div>
            </div>
        </div>
        <p class="text-xs text-gray-500 leading-relaxed">This is how active employees will see the announcement until they agree.</p>
    </div>
</div>
