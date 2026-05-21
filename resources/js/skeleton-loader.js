/**
 * Global skeleton placeholders for AJAX / fetch loading states.
 */
const AppSkeleton = {
    _cache: null,

    _loadCache() {
        if (this._cache !== null) {
            return this._cache;
        }
        this._cache = {};
        const root = document.getElementById('app-skeleton-templates');
        if (!root) {
            return this._cache;
        }
        root.querySelectorAll('template[data-skeleton]').forEach((tpl) => {
            const key = tpl.getAttribute('data-skeleton');
            if (key) {
                this._cache[key] = tpl.innerHTML.trim();
            }
        });
        return this._cache;
    },

    html(variant = 'text') {
        const cache = this._loadCache();
        return cache[variant] || cache.text || '<div class="animate-pulse space-y-2"><div class="h-4 bg-gray-200 rounded w-full"></div><div class="h-4 bg-gray-200 rounded w-3/4"></div></div>';
    },

    /**
     * @param {Element|string} target
     * @param {string} variant
     * @returns {Element|null}
     */
    render(target, variant = 'text') {
        const el = typeof target === 'string' ? document.querySelector(target) : target;
        if (!el) {
            return null;
        }
        if (!el.dataset.skeletonOriginal) {
            el.dataset.skeletonOriginal = el.innerHTML;
        }
        el.innerHTML = this.html(variant);
        el.setAttribute('aria-busy', 'true');
        return el;
    },

    clear(target) {
        const el = typeof target === 'string' ? document.querySelector(target) : target;
        if (!el) {
            return;
        }
        el.removeAttribute('aria-busy');
    },

    /**
     * @param {string} url
     * @param {{ target?: Element|string, variant?: string, restoreOnError?: boolean } & RequestInit} options
     */
    async fetch(url, options = {}) {
        const { target, variant = 'text', restoreOnError = true, ...fetchOptions } = options;
        let el = null;
        let original = '';

        if (target) {
            el = typeof target === 'string' ? document.querySelector(target) : target;
            if (el) {
                original = el.innerHTML;
                this.render(el, variant);
            }
        }

        try {
            const response = await window.fetch(url, fetchOptions);
            return response;
        } catch (err) {
            if (el && restoreOnError) {
                el.innerHTML = original;
                el.removeAttribute('aria-busy');
            }
            throw err;
        }
    },
};

window.AppSkeleton = AppSkeleton;

export default AppSkeleton;
