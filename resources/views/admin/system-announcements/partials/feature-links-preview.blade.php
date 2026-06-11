<template x-if="featureLinkPreviews().length">
    <div class="{{ $wrapperClass ?? 'px-5 pb-4 border-t border-gray-100 bg-white' }}">
        <div class="flex flex-wrap gap-2">
            <template x-for="(link, index) in featureLinkPreviews()" :key="index">
                <a :href="link.href"
                   class="{{ $linkClass ?? 'inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    <span x-text="link.label"></span>
                </a>
            </template>
        </div>
    </div>
</template>
