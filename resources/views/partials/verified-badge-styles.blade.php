{{-- Loaded after Tailwind CDN so badge sizing cannot be overridden by layered Vite CSS. --}}
<style>
    .verified-badge {
        display: inline-flex !important;
        align-items: center;
        flex-shrink: 0;
        line-height: 0;
        margin-left: 0.05em;
        vertical-align: middle;
    }

    .verified-badge .twetch-green-check {
        display: block !important;
        flex-shrink: 0 !important;
    }

    .verified-badge--xs .twetch-green-check {
        width: 10px !important;
        height: 10px !important;
        min-width: 10px !important;
        min-height: 10px !important;
        max-width: 10px !important;
        max-height: 10px !important;
    }

    .verified-badge--sm .twetch-green-check {
        width: 11px !important;
        height: 11px !important;
        min-width: 11px !important;
        min-height: 11px !important;
        max-width: 11px !important;
        max-height: 11px !important;
    }

    .verified-badge--profile .twetch-green-check {
        width: 12px !important;
        height: 12px !important;
        min-width: 12px !important;
        min-height: 12px !important;
        max-width: 12px !important;
        max-height: 12px !important;
    }

    .verified-badge:not(.verified-badge--xs):not(.verified-badge--sm):not(.verified-badge--profile) .twetch-green-check {
        width: 11px !important;
        height: 11px !important;
        min-width: 11px !important;
        min-height: 11px !important;
        max-width: 11px !important;
        max-height: 11px !important;
    }
</style>
