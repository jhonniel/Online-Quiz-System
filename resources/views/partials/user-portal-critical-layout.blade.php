{{-- Layout + form controls that must work even when Vite/Tailwind utilities fail to load --}}
<style>
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .user-portal-filter {
        box-sizing: border-box;
        width: 100%;
        padding: 1rem 1.125rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .user-portal-filter__grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1rem;
        align-items: end;
        width: 100%;
    }

    @media (min-width: 640px) {
        .user-portal-filter__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .user-portal-filter__actions {
            grid-column: 1 / -1;
        }
    }

    @media (min-width: 960px) {
        .user-portal-filter__grid {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1.15fr) minmax(0, 1fr) minmax(0, auto);
            gap: 1rem 1.25rem;
        }

        .user-portal-filter__actions {
            grid-column: auto;
            justify-self: end;
        }
    }

    .user-portal-filter__field {
        min-width: 0;
        width: 100%;
    }

    .user-portal-filter__label {
        display: block;
        margin: 0 0 0.375rem;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }

    .user-portal-filter__actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }

    @media (min-width: 480px) {
        .user-portal-filter__actions {
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
        }
    }

    @media (min-width: 960px) {
        .user-portal-filter__actions {
            flex-wrap: nowrap;
            justify-content: flex-end;
            width: auto;
            min-width: 12rem;
        }
    }

    .user-portal-control {
        box-sizing: border-box;
        display: block;
        width: 100%;
        min-height: 2.625rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #0f172a;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .user-portal-control:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }

    .user-portal-kpi {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        width: 100%;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .user-portal-kpi__cell {
        min-width: 0;
        padding: 1rem 1.125rem;
        border-right: 1px solid #f1f5f9;
    }

    .user-portal-kpi__cell:last-child {
        border-right: none;
    }

    @media (max-width: 639px) {
        .user-portal-kpi {
            grid-template-columns: minmax(0, 1fr);
        }

        .user-portal-kpi__cell {
            border-right: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .user-portal-kpi__cell:last-child {
            border-bottom: none;
        }
    }

    .user-portal-kpi__label {
        margin: 0;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }

    .user-portal-kpi__value {
        margin: 0.35rem 0 0;
        font-size: 1.375rem;
        font-weight: 600;
        line-height: 1.2;
        color: #0f172a;
        font-variant-numeric: tabular-nums;
    }

    .user-portal-kpi__value--sm {
        font-size: 1rem;
    }

    .user-portal-accordion {
        width: 100%;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .user-portal-accordion__trigger {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.75rem;
        width: 100%;
        margin: 0;
        padding: 0.875rem 1.25rem;
        text-align: left;
        cursor: pointer;
        border: none;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(90deg, #f8fafc 0%, #fff 100%);
        transition: background-color 0.15s ease;
    }

    .user-portal-accordion__trigger:hover {
        background: linear-gradient(90deg, #f1f5f9 0%, #f8fafc 100%);
    }

    .user-portal-accordion__group:last-child .user-portal-accordion__trigger--year {
        border-bottom: none;
    }

    .user-portal-accordion__trigger--month {
        background: #fff;
        padding-left: 1.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .user-portal-accordion__trigger--week {
        background: #f8fafc;
        padding-left: 2.25rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .user-portal-accordion__trigger h2,
    .user-portal-accordion__trigger h3,
    .user-portal-accordion__trigger h4 {
        margin: 0;
        flex: 1 1 auto;
        min-width: 0;
        font-weight: 600;
        color: #0f172a;
    }

    .user-portal-accordion__trigger h3 {
        font-size: 0.875rem;
        color: #1e293b;
    }

    .user-portal-accordion__trigger h4 {
        font-size: 0.875rem;
        font-weight: 500;
        color: #334155;
    }

    .user-portal-accordion__chevron {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }

    .user-portal-btn {
        box-sizing: border-box;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 2.625rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.25rem;
        text-decoration: none;
        border-radius: 0.5rem;
        cursor: pointer;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .user-portal-btn--primary {
        color: #fff;
        background: #4f46e5;
        box-shadow: 0 1px 2px rgba(79, 70, 229, 0.35);
    }

    .user-portal-btn--primary:hover {
        background: #4338ca;
    }

    .user-portal-btn--secondary {
        color: #334155;
        background: #fff;
        border-color: #cbd5e1;
    }

    .user-portal-btn--secondary:hover {
        background: #f8fafc;
    }
</style>
