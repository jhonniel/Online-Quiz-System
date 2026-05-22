/**
 * Global skeleton placeholders for AJAX / fetch loading states.
 * Skeletons are delayed by default so fast responses never flash placeholders.
 */
const AppSkeleton = {
    DEFAULT_DELAY_MS: 300,
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
     * @param {{ delayMs?: number }|number} [options]
     * @returns {Element|null}
     */
    render(target, variant = 'text', options = {}) {
        if (typeof options === 'number') {
            options = { delayMs: options };
        }
        const delayMs = options.delayMs ?? 0;
        if (delayMs > 0) {
            const token = this.beginLoading(target, variant, delayMs);
            return token.el;
        }

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

    /**
     * Show a skeleton only if loading exceeds delayMs. Call finish() when content is updated.
     * @returns {{ el: Element|null, finish: Function, cancel: Function }}
     */
    beginLoading(target, variant = 'text', delayMs = AppSkeleton.DEFAULT_DELAY_MS) {
        const el = typeof target === 'string' ? document.querySelector(target) : target;
        const noop = { el: null, finish() {}, cancel() {} };
        if (!el) {
            return noop;
        }

        let timer = null;
        let shown = false;

        const show = () => {
            if (shown) {
                return;
            }
            shown = true;
            if (!el.dataset.skeletonOriginal) {
                el.dataset.skeletonOriginal = el.innerHTML;
            }
            el.innerHTML = this.html(variant);
            el.setAttribute('aria-busy', 'true');
        };

        if (delayMs <= 0) {
            show();
        } else {
            timer = setTimeout(show, delayMs);
        }

        return {
            el,
            finish() {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
                if (el.hasAttribute('aria-busy')) {
                    el.removeAttribute('aria-busy');
                    if (el.dataset.skeletonOriginal) {
                        delete el.dataset.skeletonOriginal;
                    }
                }
            },
            cancel() {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
                if (shown && el.hasAttribute('aria-busy') && el.dataset.skeletonOriginal) {
                    el.innerHTML = el.dataset.skeletonOriginal;
                    delete el.dataset.skeletonOriginal;
                    el.removeAttribute('aria-busy');
                    shown = false;
                }
            },
        };
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
     * @param {{ target?: Element|string, variant?: string, delayMs?: number, restoreOnError?: boolean } & RequestInit} options
     */
    async fetch(url, options = {}) {
        const {
            target,
            variant = 'text',
            delayMs = this.DEFAULT_DELAY_MS,
            restoreOnError = true,
            ...fetchOptions
        } = options;
        const token = target ? this.beginLoading(target, variant, delayMs) : null;

        try {
            const response = await window.fetch(url, fetchOptions);
            return response;
        } catch (err) {
            if (token && restoreOnError) {
                token.cancel();
            }
            throw err;
        } finally {
            token?.finish();
        }
    },
};

window.AppSkeleton = AppSkeleton;

export default AppSkeleton;
