{{-- User features first, then all granted admin sections --}}
@if(auth()->user()->usesHrDashboard())
    <a href="{{ route('admin.hr-dashboard') }}"
       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hr-dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
        </svg>
        Dashboard
    </a>
@endif

@if(auth()->user()->shouldShowUserFeaturesNav())
    @include('components.nav.user-features-nav')
@endif

@if(auth()->user()->hasAnyAdminPermission() && !auth()->user()->isSuperAdmin())
<div class="pt-4 mt-4 border-t border-gray-700 mb-2" :class="sidebarCollapsed ? 'hidden' : ''">
    <p class="px-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-purple-300">Admin Features</p>
</div>
@include('components.nav.admin-nav-sections')
@endif
