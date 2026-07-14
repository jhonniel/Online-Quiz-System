{{-- Read-only merit details modal (teachers and other view-only contexts). --}}
<div id="meritDetailsModal" class="fixed inset-0 z-50 hidden" aria-hidden="true" role="dialog" aria-labelledby="meritDetailsModalTitle">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" data-merit-modal-dismiss></div>
    <div class="fixed inset-0 flex items-start justify-center p-4 sm:p-6 overflow-y-auto pointer-events-none">
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-xl border border-gray-200 pointer-events-auto my-8">
            <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-gray-100 bg-amber-50/80 rounded-t-xl">
                <div class="min-w-0">
                    <h2 id="meritDetailsModalTitle" class="text-lg font-semibold text-gray-900 truncate">Merit details</h2>
                    <p id="meritDetailsModalSubtitle" class="text-sm text-gray-600 mt-0.5 truncate"></p>
                </div>
                <button type="button" class="shrink-0 rounded-lg p-2 text-gray-500 hover:bg-white hover:text-gray-700" data-merit-modal-dismiss aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="meritDetailsModalBody" class="px-5 py-4 max-h-[min(70vh,640px)] overflow-y-auto text-sm text-gray-700">
                <div id="meritDetailsContent">
                    <p class="text-gray-500">Loading…</p>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end rounded-b-xl bg-gray-50/80">
                <button type="button" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50" data-merit-modal-dismiss>Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('meritDetailsModal');
    const content = document.getElementById('meritDetailsContent');
    const subtitle = document.getElementById('meritDetailsModalSubtitle');
    if (!modal || !content) return;

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    modal.querySelectorAll('[data-merit-modal-dismiss]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function statusBadge(status) {
        const s = String(status || '').toLowerCase();
        const colors = s === 'approved'
            ? 'bg-emerald-100 text-emerald-800'
            : (s === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
        return '<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium ' + colors + '">' + escapeHtml(status) + '</span>';
    }

    function renderDetails(data) {
        const b = data.breakdown || {};
        const absence = data.absence || {};
        const notices = data.notices || {};
        const thresholds = data.thresholds || {};
        const undertime = data.undertime_filings || [];
        const absentReqs = data.absent_requests || [];

        let noticeLines = [];
        if (notices.rules_warning) noticeLines.push('Rules violation warning (active)');
        if (notices.final_notice) noticeLines.push('Final notice (active)');
        if (notices.merit_automation_disabled) noticeLines.push('Automatic merit notices blocked');
        if (notices.student_terminated) noticeLines.push('Account terminated (access blocked)');
        if (noticeLines.length === 0) noticeLines.push('No active rules notices from merits');

        let undertimeRows = undertime.length
            ? undertime.map(function (row) {
                return '<tr class="border-t border-gray-100"><td class="py-2 pr-3">' + escapeHtml(row.date) + '</td>'
                    + '<td class="py-2 pr-3 font-mono text-xs">' + escapeHtml(row.hours_label) + '</td>'
                    + '<td class="py-2">' + statusBadge(row.status) + '</td></tr>';
            }).join('')
            : '<tr><td colspan="3" class="py-3 text-gray-500">No under-time filings below 08:00.</td></tr>';

        let absentRows = absentReqs.length
            ? absentReqs.map(function (row) {
                return '<tr class="border-t border-gray-100"><td class="py-2 pr-3">' + escapeHtml(row.range) + '</td>'
                    + '<td class="py-2 pr-3 tabular-nums">' + escapeHtml(row.days) + ' day(s)</td>'
                    + '<td class="py-2">' + statusBadge(row.status) + '</td></tr>';
            }).join('')
            : '<tr><td colspan="3" class="py-3 text-gray-500">No approved absent leave requests counting toward merits.</td></tr>';

        const teacherExcused = data.teacher_excused_absent_requests || [];
        let teacherExcusedRows = teacherExcused.length
            ? teacherExcused.map(function (row) {
                return '<tr class="border-t border-gray-100"><td class="py-2 pr-3">' + escapeHtml(row.range) + '</td>'
                    + '<td class="py-2 pr-3 tabular-nums">' + escapeHtml(row.days) + ' day(s)</td>'
                    + '<td class="py-2"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">Excluded</span></td></tr>';
            }).join('')
            : '';
        const teacherExcusedSection = teacherExcused.length
                ? '<div><h3 class="text-sm font-semibold text-gray-900 mb-2">Official Excused (teacher-filed) <span class="font-normal text-gray-500">(' + teacherExcused.length + ')</span></h3>'
                    + '<p class="text-xs text-gray-500 mb-2">Labeled Official Excused — excluded from absent days and excess absence merits.</p>'
                    + '<div class="overflow-x-auto rounded-lg border border-gray-200"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase"><tr><th class="px-3 py-2">Period</th><th class="px-3 py-2">Days</th><th class="px-3 py-2">Merit</th></tr></thead><tbody>' + teacherExcusedRows + '</tbody></table></div></div>'
                : '';

        const adminExcused = data.admin_excused_requests || [];
        let adminExcusedRows = adminExcused.length
            ? adminExcused.map(function (row) {
                return '<tr class="border-t border-gray-100"><td class="py-2 pr-3">' + escapeHtml(row.range) + '</td>'
                    + '<td class="py-2 pr-3 tabular-nums">' + escapeHtml(row.days) + ' day(s)</td>'
                    + '<td class="py-2"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">Excluded</span></td></tr>';
            }).join('')
            : '';
        const adminExcusedSection = adminExcused.length
                ? '<div><h3 class="text-sm font-semibold text-gray-900 mb-2">Excused (admin) <span class="font-normal text-gray-500">(' + adminExcused.length + ')</span></h3>'
                    + '<p class="text-xs text-gray-500 mb-2">Admin-changed Excused — excluded from absent days and excess absence merits.</p>'
                    + '<div class="overflow-x-auto rounded-lg border border-gray-200"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase"><tr><th class="px-3 py-2">Period</th><th class="px-3 py-2">Days</th><th class="px-3 py-2">Merit</th></tr></thead><tbody>' + adminExcusedRows + '</tbody></table></div></div>'
                : '';

        const meritTotal = parseInt(b.total, 10) || 0;
        const meritTotalHigh = meritTotal >= 3;
        const totalMeritBox = meritTotalHigh
            ? '<div class="rounded-lg border border-red-300 bg-red-100/50 px-3 py-2"><p class="text-xs font-medium text-red-900">Total merits</p><p class="text-2xl font-bold text-red-900 tabular-nums">' + escapeHtml(b.total ?? 0) + '</p></div>'
            : '<div class="rounded-lg border border-amber-300 bg-amber-100/50 px-3 py-2"><p class="text-xs font-medium text-amber-900">Total merits</p><p class="text-2xl font-bold text-amber-900 tabular-nums">' + escapeHtml(b.total ?? 0) + '</p></div>';

        content.innerHTML =
            '<div class="space-y-5">'
            + '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">'
            + '<div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2"><p class="text-xs text-gray-500">Under-time merits</p><p class="text-xl font-bold text-gray-900 tabular-nums">' + escapeHtml(b.undertime ?? 0) + '</p></div>'
            + '<div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2"><p class="text-xs text-gray-500">Excess absences</p><p class="text-xl font-bold text-gray-900 tabular-nums">' + escapeHtml(b.excess_absence ?? 0) + '</p></div>'
            + '<div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2"><p class="text-xs text-gray-500">Manual</p><p class="text-xl font-bold text-gray-900 tabular-nums">' + escapeHtml(b.manual ?? 0) + '</p></div>'
            + totalMeritBox
            + '</div>'
            + '<p class="text-xs text-gray-500">Auto notices (system): violation warning at <strong>' + escapeHtml(thresholds.warning ?? 1) + '+</strong> merits; final notice at <strong>' + escapeHtml(thresholds.final ?? 3) + '+</strong> merits.</p>'
            + '<div class="rounded-lg border border-gray-200 p-3"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Rules notices</p><ul class="text-sm text-gray-700 list-disc list-inside">' + noticeLines.map(function (l) { return '<li>' + escapeHtml(l) + '</li>'; }).join('') + '</ul></div>'
            + '<div><h3 class="text-sm font-semibold text-gray-900 mb-1">Allowable absences</h3>'
            + '<dl class="grid grid-cols-2 gap-2 text-sm"><div><dt class="text-gray-500">Balance allowed</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.allowable) + ' days</dd></div>'
            + '<div><dt class="text-gray-500">Approved absent days</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.approved_days) + '</dd></div>'
            + '<div><dt class="text-gray-500">Remaining</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.remaining_balance) + ' days</dd></div>'
            + '<div><dt class="text-gray-500">Days over balance</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.days_over_balance ?? absence.excess_merits) + '</dd></div>'
            + '<div><dt class="text-gray-500">Excess absence merits</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.excess_merits) + '</dd></div></dl>'
            + '<p class="mt-2 text-xs text-gray-500">' + escapeHtml(data.rules?.excess_absence || '') + '</p></div>'
            + '<div><h3 class="text-sm font-semibold text-gray-900 mb-2">Under-time time requests <span class="font-normal text-gray-500">(' + undertime.length + ')</span></h3>'
            + '<p class="text-xs text-gray-500 mb-2">' + escapeHtml(data.rules?.undertime || '') + '</p>'
            + '<div class="overflow-x-auto rounded-lg border border-gray-200"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase"><tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Filed</th><th class="px-3 py-2">Status</th></tr></thead><tbody class="px-3">' + undertimeRows + '</tbody></table></div></div>'
            + '<div><h3 class="text-sm font-semibold text-gray-900 mb-2">Approved absent leave <span class="font-normal text-gray-500">(' + absentReqs.length + ')</span></h3>'
            + '<div class="overflow-x-auto rounded-lg border border-gray-200"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase"><tr><th class="px-3 py-2">Period</th><th class="px-3 py-2">Days</th><th class="px-3 py-2">Status</th></tr></thead><tbody>' + absentRows + '</tbody></table></div></div>'
            + teacherExcusedSection
            + adminExcusedSection
            + '<p class="text-xs text-gray-500">' + escapeHtml(data.rules?.manual || '') + '</p>'
            + '</div>';
    }

    document.querySelectorAll('.merit-details-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = btn.getAttribute('data-merits-url');
            if (!url) return;

            openModal();
            content.innerHTML = '<p class="text-gray-500 py-6 text-center">Loading merit details…</p>';
            subtitle.textContent = '';

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('Failed to load');
                    return res.json();
                })
                .then(function (data) {
                    const student = data.student || {};
                    subtitle.textContent = (student.name || '') + (student.email ? ' · ' + student.email : '');
                    renderDetails(data);
                })
                .catch(function () {
                    content.innerHTML = '<p class="text-red-600 py-4">Could not load merit details. Please try again.</p>';
                });
        });
    });
})();
</script>
