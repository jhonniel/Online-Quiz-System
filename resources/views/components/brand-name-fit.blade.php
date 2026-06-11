@once
<style>
    [data-sidebar-brand-root] .brand-name-fit-container {
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
    }

    [data-sidebar-brand-root] .brand-name-fit {
        display: block;
        width: max-content;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
    }
</style>
<script>
    function fitBrandNames(root) {
        const brandRoots = [];

        if (root && root instanceof Element) {
            if (root.matches('[data-sidebar-brand-root]')) {
                brandRoots.push(root);
            } else if (typeof root.querySelectorAll === 'function') {
                root.querySelectorAll('[data-sidebar-brand-root]').forEach(function (el) {
                    brandRoots.push(el);
                });
            }
        } else {
            document.querySelectorAll('[data-sidebar-brand-root]').forEach(function (el) {
                brandRoots.push(el);
            });
        }

        brandRoots.forEach(function (brandRoot) {
            brandRoot.querySelectorAll('.brand-name-fit').forEach(function (el) {
                const container = el.closest('.brand-name-fit-container');

                if (!container || container.clientWidth === 0) {
                    return;
                }

                if (!el.dataset.fitBaseSize) {
                    el.dataset.fitBaseSize = el.style.fontSize || window.getComputedStyle(el).fontSize;
                }

                el.style.fontSize = el.dataset.fitBaseSize;

                const maxWidth = container.clientWidth;
                let size = parseFloat(window.getComputedStyle(el).fontSize) || 18;
                const minSize = parseFloat(el.dataset.fitMinSize || '10');

                while (el.scrollWidth > maxWidth && size > minSize) {
                    size -= 0.5;
                    el.style.fontSize = size + 'px';
                }
            });
        });
    }

    function scheduleBrandNameFit(root) {
        requestAnimationFrame(function () {
            fitBrandNames(root);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        scheduleBrandNameFit();
    });

    window.addEventListener('resize', function () {
        scheduleBrandNameFit();
    });

    window.addEventListener('sidebar-collapse-changed', function () {
        window.setTimeout(function () {
            scheduleBrandNameFit();
        }, 320);
    });
</script>
@endonce
