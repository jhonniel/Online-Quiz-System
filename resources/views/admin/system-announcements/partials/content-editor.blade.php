<div class="space-y-5">
    <div>
        <label for="title" class="block text-sm font-semibold text-gray-700 mb-1.5">Title</label>
        <input type="text" name="title" id="title" x-model="title" required maxlength="255"
               placeholder="e.g. System update — June 2026"
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        @error('title')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <div class="flex items-center justify-between gap-3 mb-1.5">
            <label for="content" class="block text-sm font-semibold text-gray-700">Message</label>
            <span class="text-[11px] font-medium uppercase tracking-wide text-indigo-600">Bullet-friendly</span>
        </div>
        <textarea name="content" id="content" rows="14" required maxlength="65000" x-model="content"
                  placeholder="- First update point&#10;- Second update point&#10;&#10;Optional closing paragraph."
                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm leading-relaxed font-mono"></textarea>
        <div class="mt-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2.5">
            <p class="text-xs font-semibold text-slate-700">Formatting</p>
            <ul class="mt-1 text-xs text-slate-600 space-y-0.5 list-disc list-inside">
                <li>Start a line with <code class="text-[11px] bg-white px-1 rounded border">-</code> for bullet points</li>
                <li>Use <code class="text-[11px] bg-white px-1 rounded border">1.</code> for numbered lists</li>
                <li>Blank lines separate paragraphs</li>
            </ul>
        </div>
        @error('content')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="xl:hidden">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Employee popup preview</p>
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 bg-gray-50">
                <p class="text-sm font-bold text-gray-900 text-center" x-text="title || 'Announcement title'"></p>
                <p class="text-[11px] text-gray-500 text-center mt-0.5">Please read this announcement.</p>
            </div>
            <div class="px-4 py-3 text-sm text-gray-700 leading-relaxed max-h-48 overflow-y-auto employee-announcement-content" x-html="formatPreview(content)"></div>
        </div>
    </div>
</div>
