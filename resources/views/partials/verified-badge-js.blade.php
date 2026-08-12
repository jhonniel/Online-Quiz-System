{{-- Shared verified-badge helper for JS-rendered names (chat, search, Alpine). --}}
<script>
(function () {
    if (window.VerifiedBadgeUI) {
        return;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function badgeSvg(size) {
        const s = Math.max(10, Math.min(12, Number(size) || 11));
        const gradId = 'vb-js-' + Math.random().toString(36).slice(2, 10);
        const sizeClass = s <= 10 ? 'verified-badge--xs' : (s >= 12 ? 'verified-badge--profile' : 'verified-badge--sm');
        const svgStyle = 'width:' + s + 'px!important;height:' + s + 'px!important;min-width:' + s + 'px;min-height:' + s + 'px;max-width:' + s + 'px;max-height:' + s + 'px;flex-shrink:0;';
        return '<span class="verified-badge ' + sizeClass + ' inline-flex items-center flex-shrink-0" title="Verified account" role="img" aria-label="Verified account">'
            + '<svg viewBox="0 0 24 24" width="' + s + '" height="' + s + '" class="twetch-green-check" aria-hidden="true" focusable="false" style="' + svgStyle + '">'
            + '<path d="M6.66491 19.6329H8.57533C8.7514 19.6329 8.88346 19.6857 9.01552 19.8178L10.3713 21.1647C11.4806 22.2828 12.5106 22.274 13.6199 21.1647L14.9757 19.8178C15.1165 19.6857 15.2398 19.6329 15.4247 19.6329H17.3263C18.9022 19.6329 19.6329 18.911 19.6329 17.3263V15.4247C19.6329 15.2398 19.6857 15.1165 19.8178 14.9757L21.1647 13.6199C22.2828 12.5106 22.274 11.4806 21.1647 10.3713L19.8178 9.01552C19.6857 8.88346 19.6329 8.7514 19.6329 8.57533V6.66491C19.6329 5.09783 18.911 4.35832 17.3263 4.35832H15.4247C15.2398 4.35832 15.1165 4.3143 14.9757 4.18224L13.6199 2.83526C12.5106 1.71718 11.4806 1.72599 10.3713 2.83526L9.01552 4.18224C8.88346 4.3143 8.7514 4.35832 8.57533 4.35832H6.66491C5.08903 4.35832 4.35832 5.08023 4.35832 6.66491V8.57533C4.35832 8.7514 4.3143 8.88346 4.18224 9.01552L2.83526 10.3713C1.71718 11.4806 1.72599 12.5106 2.83526 13.6199L4.18224 14.9757C4.3143 15.1165 4.35832 15.2398 4.35832 15.4247V17.3263C4.35832 18.9022 5.08903 19.6329 6.66491 19.6329Z" fill="url(#' + gradId + ')"></path>'
            + '<path d="M11.014 16.2962C10.7146 16.2962 10.4681 16.1818 10.2392 15.8737L8.02949 13.1621C7.89744 12.986 7.8182 12.7835 7.8182 12.5899C7.8182 12.1849 8.12634 11.8591 8.53131 11.8591C8.77782 11.8591 8.9715 11.9472 9.19159 12.2377L10.9788 14.5443L14.738 8.5049C14.9052 8.23198 15.1341 8.09992 15.3718 8.09992C15.7504 8.09992 16.1114 8.36404 16.1114 8.76901C16.1114 8.9715 15.9969 9.17399 15.8913 9.35006L11.7535 15.8737C11.5686 16.1554 11.3133 16.2962 11.014 16.2962Z" fill="#1b1c25"></path>'
            + '<defs><linearGradient id="' + gradId + '" x1="2" y1="2.8" x2="21.2" y2="22" gradientUnits="userSpaceOnUse"><stop stop-color="#A1FF8B"></stop><stop offset="1" stop-color="#34D399"></stop></linearGradient></defs>'
            + '</svg></span>';
    }

    window.VerifiedBadgeUI = {
        badgeHtml: badgeSvg,
        nameHtml: function (name, verified, size) {
            const safeName = escapeHtml(name);
            if (!verified) {
                return safeName;
            }
            return '<span class="inline-flex items-center gap-0 min-w-0 max-w-full align-middle">'
                + '<span class="truncate">' + safeName + '</span>'
                + badgeSvg(size || 12)
                + '</span>';
        },
        isVerified: function (userOrFlag) {
            if (typeof userOrFlag === 'boolean') {
                return userOrFlag;
            }
            if (!userOrFlag || typeof userOrFlag !== 'object') {
                return false;
            }
            return !!(userOrFlag.profile_verified || userOrFlag.has_verified_badge || userOrFlag.verified);
        }
    };
})();
</script>
