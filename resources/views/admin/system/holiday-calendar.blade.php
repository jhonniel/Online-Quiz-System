@extends('layouts.admin')

@section('page-title', 'Holiday Calendar')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">System</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Calendar</span>
</li>
@endsection

@section('content')
@php
    use App\Models\DtrHoliday;
    $editing = $editHoliday ?? null;
    $formDate = old('date', $editing?->date?->format('Y-m-d') ?? ($prefillDate ?? ''));
    $formName = old('name', $editing?->name ?? '');
    $formType = old('type', $editing?->type ?? DtrHoliday::TYPE_REGULAR);
    $formNotes = old('notes', $editing?->notes ?? '');
    $shouldOpenModal = $editing || $errors->any() || !empty($prefillDate);
    $holidayTypes = DtrHoliday::typeOptions();
@endphp
<div class="space-y-6 min-w-0 px-3 sm:px-4 lg:px-6">
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold">Holiday Calendar</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1 max-w-2xl">
                        Mark official holidays here. On these dates, employee DTR views auto-credit <strong class="text-white">8 hours</strong> with status <strong class="text-white">Holiday</strong> when no time entry exists.
                    </p>
                </div>
            </div>
            <button type="button" id="openAddHolidayBtn"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-white text-indigo-700 px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-indigo-50 transition-colors shrink-0">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                Add holiday
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 lg:gap-6 items-stretch">
        <div class="xl:col-span-1">
            <div class="bg-amber-50 rounded-2xl border border-amber-200 p-4">
                <h3 class="text-xs font-bold uppercase tracking-wide text-amber-900">Holidays this month</h3>
                @if($monthHolidays->isEmpty())
                    <p class="mt-2 text-sm text-amber-800">No holidays set for {{ $currentMonth->format('F Y') }}.</p>
                    <button type="button"
                            class="mt-3 text-sm font-medium text-indigo-700 hover:text-indigo-900 holiday-add-trigger">
                        + Add your first holiday
                    </button>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach($monthHolidays as $holiday)
                            @php
                                $isSpecial = $holiday->isSpecial();
                                $listBorder = $isSpecial ? 'border-violet-200' : 'border-amber-200';
                                $typeBadge = $isSpecial
                                    ? 'bg-violet-100 text-violet-800 border-violet-200'
                                    : 'bg-amber-100 text-amber-900 border-amber-200';
                            @endphp
                            <li class="rounded-lg border {{ $listBorder }} bg-white/70 px-3 py-2.5">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $typeBadge }}">
                                                {{ $holiday->typeLabel() }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-900 leading-snug break-words">{{ $holiday->name }}</p>
                                        <p class="mt-0.5 text-xs text-gray-600">{{ $holiday->date->format('M j, Y') }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                                        <button type="button"
                                                class="holiday-edit-trigger inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-800 whitespace-nowrap"
                                                data-id="{{ $holiday->id }}"
                                                data-update-url="{{ route('admin.system.calendar.update', $holiday) }}"
                                                data-date="{{ $holiday->date->format('Y-m-d') }}"
                                                data-name="{{ e($holiday->name) }}"
                                                data-type="{{ $holiday->type }}"
                                                data-notes="{{ e($holiday->notes ?? '') }}">Edit</button>
                                        <form method="POST" action="{{ route('admin.system.calendar.destroy', $holiday) }}"
                                              class="inline-flex items-center m-0 p-0"
                                              onsubmit="return confirm('Remove this holiday?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center text-xs font-medium text-red-600 hover:text-red-800 whitespace-nowrap bg-transparent border-0 p-0 cursor-pointer">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="xl:col-span-3 flex flex-col space-y-3 min-h-0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.system.calendar.index', ['month' => $prevMonth]) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </a>
                    <span class="text-lg font-semibold text-gray-900 px-1">{{ $currentMonth->format('F Y') }}</span>
                    <a href="{{ route('admin.system.calendar.index', ['month' => $nextMonth]) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50">
                        Next
                        <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Regular Holiday</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span> Special Holiday</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col flex-1 min-h-0">
                <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $weekday)
                        <div class="px-2 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">{{ $weekday }}</div>
                    @endforeach
                </div>
                <div class="divide-y divide-gray-200 flex-1 flex flex-col min-h-0">
                    @foreach($weeks as $week)
                        <div class="grid grid-cols-7 flex-1 h-full min-h-0">
                            @foreach($week as $day)
                                @php
                                    $date = $day['date'];
                                    $holiday = $day['holiday'] ?? null;
                                    $isCurrentMonth = $date->format('Y-m') === $currentMonth->format('Y-m');
                                    $isToday = $date->isSameDay(\Carbon\Carbon::now('Asia/Manila'));
                                    $isWeekend = $date->isWeekend();
                                    $holidayIsSpecial = $holiday?->isSpecial() ?? false;
                                    $cellHolidayBg = $holiday
                                        ? ($holidayIsSpecial ? 'bg-violet-50' : 'bg-amber-50')
                                        : ($isCurrentMonth ? ($isToday ? 'bg-emerald-50/60' : 'bg-white') : 'bg-gray-50/80');
                                    $chipClasses = $holidayIsSpecial
                                        ? 'border-violet-300 bg-violet-100/80 text-violet-900 hover:bg-violet-200/80'
                                        : 'border-amber-300 bg-amber-100/80 text-amber-900 hover:bg-amber-200/80';
                                @endphp
                                <div class="border-r border-gray-100 last:border-r-0 p-1.5 sm:p-2 text-xs flex flex-col group h-full min-h-0 {{ $cellHolidayBg }}
                                    {{ $isWeekend && !$holiday ? 'opacity-70' : '' }}">
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        @if($isCurrentMonth && !$isWeekend && !$holiday)
                                            <button type="button"
                                                    class="holiday-add-trigger font-semibold text-sm text-gray-900 hover:text-indigo-600"
                                                    data-date="{{ $date->format('Y-m-d') }}">
                                                {{ $date->format('j') }}
                                            </button>
                                        @else
                                            <span class="font-semibold text-sm {{ $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $date->format('j') }}
                                            </span>
                                        @endif
                                        @if($isToday)
                                            <span class="text-[10px] font-semibold text-emerald-700">Today</span>
                                        @endif
                                    </div>
                                    @if($holiday)
                                        <button type="button"
                                                class="holiday-edit-trigger mt-auto w-full text-left rounded-md border px-1.5 py-1 text-[10px] sm:text-[11px] font-semibold truncate {{ $chipClasses }}"
                                                title="{{ $holiday->typeLabel() }}: {{ $holiday->name }}"
                                                data-id="{{ $holiday->id }}"
                                                data-update-url="{{ route('admin.system.calendar.update', $holiday) }}"
                                                data-date="{{ $holiday->date->format('Y-m-d') }}"
                                                data-name="{{ e($holiday->name) }}"
                                                data-type="{{ $holiday->type }}"
                                                data-notes="{{ e($holiday->notes ?? '') }}">
                                            {{ $holiday->name }}
                                        </button>
                                    @elseif($isCurrentMonth && !$isWeekend)
                                        <button type="button"
                                                class="holiday-add-trigger mt-auto text-[10px] text-indigo-600 hover:text-indigo-800 font-medium opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity"
                                                data-date="{{ $date->format('Y-m-d') }}">
                                            + Add
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add / edit holiday modal --}}
<div id="holidayModal"
     class="fixed inset-0 z-50 {{ $shouldOpenModal ? '' : 'hidden' }}"
     aria-hidden="{{ $shouldOpenModal ? 'false' : 'true' }}"
     role="dialog"
     aria-labelledby="holidayModalTitle"
     data-default-type="{{ DtrHoliday::TYPE_REGULAR }}"
     data-store-url="{{ route('admin.system.calendar.store') }}"
     data-should-open-on-load="{{ $shouldOpenModal ? '1' : '0' }}">
    <div id="holidayModalBackdrop"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity duration-200 ease-out {{ $shouldOpenModal ? 'opacity-100' : 'opacity-0' }}"
         data-holiday-modal-dismiss></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div id="holidayModalPanel"
             class="relative w-full max-w-md rounded-xl bg-white shadow-xl border border-gray-200 pointer-events-auto transition-all duration-200 ease-out {{ $shouldOpenModal ? 'opacity-100 scale-100 translate-y-0' : 'opacity-0 scale-95 translate-y-3 sm:translate-y-2 sm:scale-[0.98]' }}">
            <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
                <div>
                    <h2 id="holidayModalTitle" class="text-base font-semibold text-gray-900">{{ $editing ? 'Edit holiday' : 'Add holiday' }}</h2>
                    <p id="holidayModalSubtitle" class="mt-0.5 text-sm text-gray-500">Employees receive 8 hours on this date when no DTR entry exists.</p>
                </div>
                <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700" data-holiday-modal-dismiss aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="holidayForm" method="POST"
                  action="{{ $editing ? route('admin.system.calendar.update', $editing) : route('admin.system.calendar.store') }}"
                  class="px-5 py-4 space-y-3">
                @csrf
                <input type="hidden" name="_method" id="holidayFormMethod" value="{{ $editing ? 'PUT' : 'POST' }}" {{ $editing ? '' : 'disabled' }}>
                <input type="hidden" name="holiday_id" id="holiday_id" value="{{ $editing?->id ?? old('holiday_id') }}" {{ $editing ? '' : 'disabled' }}>

                @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="holiday_date" class="block text-xs font-semibold text-gray-700 mb-1">Date</label>
                    <input type="date" name="date" id="holiday_date" value="{{ $formDate }}" required
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 @error('date') border-red-500 @enderror">
                    @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="holiday_name" class="block text-xs font-semibold text-gray-700 mb-1">Holiday name</label>
                    <input type="text" name="name" id="holiday_name" value="{{ $formName }}" required maxlength="255"
                           placeholder="e.g. Independence Day"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="holiday_type" class="block text-xs font-semibold text-gray-700 mb-1">Holiday type</label>
                    <select name="type" id="holiday_type" required
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white @error('type') border-red-500 @enderror">
                        @foreach($holidayTypes as $value => $label)
                            <option value="{{ $value }}" {{ $formType === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="holiday_notes" class="block text-xs font-semibold text-gray-700 mb-1">Notes <span class="font-normal text-gray-400">(optional)</span></label>
                    <textarea name="notes" id="holiday_notes" rows="2" maxlength="2000"
                              class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                              placeholder="Internal note">{{ $formNotes }}</textarea>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" data-holiday-modal-dismiss
                            class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="holidayFormSubmit"
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                        {{ $editing ? 'Update holiday' : 'Save holiday' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('holidayModal');
        const form = document.getElementById('holidayForm');
        const methodInput = document.getElementById('holidayFormMethod');
        const titleEl = document.getElementById('holidayModalTitle');
        const submitBtn = document.getElementById('holidayFormSubmit');
        const dateInput = document.getElementById('holiday_date');
        const nameInput = document.getElementById('holiday_name');
        const typeInput = document.getElementById('holiday_type');
        const notesInput = document.getElementById('holiday_notes');
        const holidayIdInput = document.getElementById('holiday_id');
        const defaultType = modal?.dataset.defaultType ?? '';
        const storeUrl = modal?.dataset.storeUrl ?? '';
        const shouldOpenOnLoad = (modal?.dataset.shouldOpenOnLoad ?? '0') === '1';
        const backdrop = document.getElementById('holidayModalBackdrop');
        const panel = document.getElementById('holidayModalPanel');
        const ANIM_MS = 200;

        if (!modal || !form) return;

        function playOpenAnimation() {
            backdrop?.classList.remove('opacity-0');
            backdrop?.classList.add('opacity-100');
            panel?.classList.remove('opacity-0', 'scale-95', 'translate-y-3', 'sm:translate-y-2', 'sm:scale-[0.98]');
            panel?.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }

        function playCloseAnimation(callback) {
            backdrop?.classList.remove('opacity-100');
            backdrop?.classList.add('opacity-0');
            panel?.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            panel?.classList.add('opacity-0', 'scale-95', 'translate-y-3', 'sm:translate-y-2', 'sm:scale-[0.98]');
            window.setTimeout(callback, ANIM_MS);
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            requestAnimationFrame(function () {
                requestAnimationFrame(playOpenAnimation);
            });
            window.setTimeout(function () {
                (nameInput && !nameInput.value ? nameInput : dateInput)?.focus();
            }, ANIM_MS + 20);
        }

        function closeModal() {
            playCloseAnimation(function () {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            });
        }

        function setEditMode(updateUrl, data) {
            form.action = updateUrl;
            if (methodInput) {
                methodInput.value = 'PUT';
                methodInput.disabled = false;
            }
            if (holidayIdInput) {
                holidayIdInput.value = data.id || '';
                holidayIdInput.disabled = false;
            }
            if (titleEl) titleEl.textContent = 'Edit holiday';
            if (submitBtn) submitBtn.textContent = 'Update holiday';
            if (dateInput) dateInput.value = data.date || '';
            if (nameInput) nameInput.value = data.name || '';
            if (typeInput) typeInput.value = data.type || defaultType;
            if (notesInput) notesInput.value = data.notes || '';
        }

        function setAddMode(date) {
            form.action = storeUrl;
            if (methodInput) {
                methodInput.value = 'POST';
                methodInput.disabled = true;
            }
            if (holidayIdInput) {
                holidayIdInput.value = '';
                holidayIdInput.disabled = true;
            }
            if (titleEl) titleEl.textContent = 'Add holiday';
            if (submitBtn) submitBtn.textContent = 'Save holiday';
            if (dateInput) dateInput.value = date || '';
            if (nameInput) nameInput.value = '';
            if (typeInput) typeInput.value = defaultType;
            if (notesInput) notesInput.value = '';
        }

        modal.querySelectorAll('[data-holiday-modal-dismiss]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden') && modal.getAttribute('aria-hidden') !== 'true') {
                closeModal();
            }
        });

        document.querySelectorAll('.holiday-add-trigger').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setAddMode(btn.getAttribute('data-date') || '');
                openModal();
            });
        });

        document.getElementById('openAddHolidayBtn')?.addEventListener('click', function () {
            setAddMode('');
            openModal();
        });

        document.querySelectorAll('.holiday-edit-trigger').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setEditMode(btn.getAttribute('data-update-url'), {
                    id: btn.getAttribute('data-id') || '',
                    date: btn.getAttribute('data-date') || '',
                    name: btn.getAttribute('data-name') || '',
                    type: btn.getAttribute('data-type') || defaultType,
                    notes: btn.getAttribute('data-notes') || '',
                });
                openModal();
            });
        });

        if (shouldOpenOnLoad) {
            document.body.classList.add('overflow-hidden');
            window.setTimeout(function () {
                (nameInput && !nameInput.value ? nameInput : dateInput)?.focus();
            }, 50);
        }
    });
</script>
@endsection
