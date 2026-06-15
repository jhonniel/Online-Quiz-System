{{-- User features first, then all granted admin sections --}}
@if(auth()->user()->shouldShowUserFeaturesNav())
    @include('components.nav.user-features-nav')
@endif

@if(auth()->user()->hasAnyAdminPermission() && !auth()->user()->isSuperAdmin())
<div class="pt-4 mt-4 border-t border-gray-700 mb-2" :class="sidebarCollapsed ? 'hidden' : ''">
    <p class="px-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-purple-300">Admin Features</p>
</div>
@include('components.nav.admin-nav-sections')
@endif
