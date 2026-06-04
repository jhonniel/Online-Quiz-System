<script>
window.getUserThemeChartColors = function () {
    const defaults = {
        primary: '#6366F1',
        dark: '#4F46E5',
        mid: '#818CF8',
        light: '#A5B4FC',
        palette: ['#6366F1', '#4F46E5', '#818CF8', '#A5B4FC'],
        fill: 'rgba(99, 102, 241, 0.15)',
        grid: '#E5E7EB',
        border: '#6366F1',
    };

    if (!document.body.classList.contains('user-theme-custom')) {
        return defaults;
    }

    const style = getComputedStyle(document.body);
    const primary = style.getPropertyValue('--user-theme').trim() || defaults.primary;
    const dark = style.getPropertyValue('--user-theme-dark').trim() || primary;
    const mid = style.getPropertyValue('--user-theme-mid').trim() || primary;
    const light = style.getPropertyValue('--user-theme-light').trim() || primary;

    function hexToRgba(hex, alpha) {
        const normalized = hex.replace('#', '');
        if (normalized.length !== 6) {
            return defaults.fill;
        }
        const r = parseInt(normalized.slice(0, 2), 16);
        const g = parseInt(normalized.slice(2, 4), 16);
        const b = parseInt(normalized.slice(4, 6), 16);
        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    return {
        primary: primary,
        dark: dark,
        mid: mid,
        light: light,
        palette: [primary, dark, mid, light],
        fill: hexToRgba(primary, 0.15),
        grid: hexToRgba(primary, 0.12),
        border: primary,
    };
};
</script>
