@extends('layouts.admin')

@section('scripts')
<script>
// Define showTab function immediately - must be available before buttons are clicked
(function() {
    'use strict';

    window.showTab = function(tabName) {
        console.log('=== showTab called with:', tabName, '===');

        try {
            // Hide all tab contents
            const allContents = document.querySelectorAll('.tab-content');
            console.log('Found', allContents.length, 'tab content elements');
            allContents.forEach(function(content) {
                content.classList.add('hidden');
                content.style.display = 'none';
            });

            // Remove active class from all tabs
            const allTabs = document.querySelectorAll('.settings-tab');
            console.log('Found', allTabs.length, 'tab buttons');
            allTabs.forEach(function(tab) {
                tab.classList.remove('active');
            });

            // Show selected tab content
            const contentId = 'content-' + tabName;
            const contentElement = document.getElementById(contentId);
            console.log('Looking for element with ID:', contentId);
            console.log('Element found:', contentElement);

            if (contentElement) {
                contentElement.classList.remove('hidden');
                contentElement.style.display = '';
                console.log('✓ Tab content shown:', contentId);
            } else {
                console.error('✗ Tab content NOT found:', contentId);
                // List all available content IDs
                const allContentIds = [];
                document.querySelectorAll('.tab-content').forEach(function(el) {
                    allContentIds.push(el.id);
                });
                console.error('Available content IDs:', allContentIds);
                alert('Tab content not found: ' + contentId + '\nAvailable: ' + allContentIds.join(', '));
                return false;
            }

            // Add active class to selected tab
            const tabId = 'tab-' + tabName;
            const tabElement = document.getElementById(tabId);
            console.log('Looking for tab button with ID:', tabId);
            console.log('Tab button found:', tabElement);

            if (tabElement) {
                tabElement.classList.add('active');
                console.log('✓ Tab button activated:', tabId);
            } else {
                console.error('✗ Tab button NOT found:', tabId);
                alert('Tab button not found: ' + tabId);
                return false;
            }

            console.log('=== Tab switch completed successfully ===');
            return true;
        } catch (error) {
            console.error('Error in showTab:', error);
            alert('Error switching tab: ' + error.message);
            return false;
        }
    };

    console.log('showTab function defined and ready');
})();
</script>
@endsection

@push('styles')
<style>
    .settings-tab {
        @apply px-6 py-3 text-sm font-medium rounded-t-lg transition-all duration-200;
    }
    .settings-tab.active {
        @apply bg-white text-indigo-600 border-b-2 border-indigo-600;
    }
    .settings-tab:not(.active) {
        @apply text-gray-600 hover:text-gray-900 hover:bg-gray-50;
    }
    .settings-tab {
        cursor: pointer !important;
        pointer-events: auto !important;
        position: relative;
    }
    
    /* Ensure settings container and tabs are properly positioned - above sidebar */
    .bg-white.rounded-2xl.shadow-xl {
        position: relative;
        z-index: 51 !important; /* Higher than sidebar z-50 */
    }
    
    /* Tab navigation wrapper - ensure it's above content and sidebar */
    .border-b.border-gray-200.bg-gray-50 {
        position: relative;
        z-index: 52 !important; /* Higher than container */
        overflow: visible;
    }
    
    /* Ensure nav element is properly positioned */
    nav[aria-label="Tabs"] {
        position: relative;
        z-index: 52 !important;
    }
    
    /* Ensure tab buttons are clickable and visible */
    .settings-tab {
        position: relative;
        z-index: 53 !important;
    }
    .form-section {
        @apply bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-6 shadow-sm;
    }
    .input-group {
        @apply relative;
    }
    .input-icon {
        @apply absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400;
    }
    .input-with-icon {
        @apply pl-10;
    }
</style>
@endpush


@section('content')
<script>
// Define showTab immediately at the start of content - ensures it's available
if (!window.showTab) {
    window.showTab = function(tabName) {
        console.log('showTab called:', tabName);
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.settings-tab').forEach(el => el.classList.remove('active'));
        const content = document.getElementById('content-' + tabName);
        const tab = document.getElementById('tab-' + tabName);
        if (content) {
            content.classList.remove('hidden');
            content.style.display = '';
        }
        if (tab) {
            tab.classList.add('active');
        }
    };
    console.log('showTab defined inline');
}
</script>
<div class="space-y-6" style="position: relative; z-index: 1;">
    <!-- Enhanced Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
                <div>
                    <h1 class="text-3xl font-bold">System Settings</h1>
                    <p class="text-indigo-100 mt-1">Configure and customize your quiz system</p>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2 border border-white/20">
                    <p class="text-sm text-indigo-100">Last updated</p>
                    <p class="text-white font-semibold">{{ now()->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Settings Form with Tabs -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden" style="position: relative; z-index: 1;">
        <!-- Tab Navigation (Outside form to prevent interference) -->
        <div class="border-b border-gray-200 bg-gray-50 px-6" style="position: relative; z-index: 2; overflow: visible;">
            <nav class="flex space-x-1 -mb-px" aria-label="Tabs" style="position: relative; z-index: 2;">
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); if(window.showTab) window.showTab('general'); return false;" id="tab-general" class="settings-tab active">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"></path>
                        </svg>
                        <span>General</span>
                    </div>
                </button>
                <button type="button" onclick="console.log('HIRING BUTTON CLICKED!'); event.preventDefault(); event.stopPropagation(); event.stopImmediatePropagation(); if(window.showTab) { console.log('Calling showTab...'); window.showTab('hiring'); } else { console.error('showTab function not found!'); alert('showTab function not found!'); } return false;" id="tab-hiring" class="settings-tab">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Hiring Process</span>
                    </div>
                </button>
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); if(window.showTab) window.showTab('email'); return false;" id="tab-email" class="settings-tab">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Email</span>
                    </div>
                </button>
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); if(window.showTab) window.showTab('contact'); return false;" id="tab-contact" class="settings-tab">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Contact</span>
                    </div>
                </button>
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); if(window.showTab) window.showTab('health'); return false;" id="tab-health" class="settings-tab">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>System Health</span>
                    </div>
                </button>
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); if(window.showTab) window.showTab('maintenance'); return false;" id="tab-maintenance" class="settings-tab">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Maintenance</span>
                    </div>
                </button>
            </nav>
        </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

            <div class="p-8">
                <!-- General Settings Tab -->
                <div id="content-general" class="tab-content">
                <div class="space-y-8">
                        <!-- Branding Section -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                    </svg>
                                </div>
                    <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Branding & Identity</h3>
                                    <p class="text-sm text-gray-500">Customize your system's visual identity</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="input-group">
                                    <label for="system_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            System Name
                                        </span>
                                    </label>
                            <input type="text" name="system_name" id="system_name" required
                                           value="{{ $settings['system_name'] }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200">
                                    <p class="mt-2 text-xs text-gray-500">Displayed in navigation and page titles</p>
                        </div>

                    <div>
                                    <label for="system_description" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                            </svg>
                                            System Description
                                        </span>
                                    </label>
                                    <textarea name="system_description" id="system_description" rows="3"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200">{{ $settings['system_description'] }}</textarea>
                                    <p class="mt-2 text-xs text-gray-500">Brief description of your quiz system</p>
                    </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                                        <label for="system_logo" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center">
                                                <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                System Logo
                                            </span>
                                        </label>
                            @if($settings['system_logo'])
                                            <div class="mb-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                <img src="{{ Storage::url($settings['system_logo']) }}" alt="Current Logo" class="h-16 w-auto object-contain mx-auto">
                                </div>
                            @endif
                                        <div class="mt-2">
                            <input type="file" name="system_logo" id="system_logo" accept="image/*"
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        @if($settings['system_logo'])
                                            <label class="mt-3 flex items-center text-sm text-red-600 cursor-pointer">
                                                <input type="checkbox" name="remove_logo" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mr-2">
                                                Remove current logo
                                </label>
                        @endif
                    </div>

                    <div>
                                        <label for="system_icon" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center">
                                                <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                                </svg>
                                                Favicon
                                            </span>
                                        </label>
                            @if(isset($settings['system_icon']) && $settings['system_icon'])
                                            <div class="mb-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                <img src="{{ Storage::url($settings['system_icon']) }}" alt="Current Icon" class="h-8 w-8 object-contain mx-auto">
                                </div>
                            @endif
                                        <div class="mt-2">
                            <input type="file" name="system_icon" id="system_icon" accept="image/*"
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        @if(isset($settings['system_icon']) && $settings['system_icon'])
                                            <label class="mt-3 flex items-center text-sm text-red-600 cursor-pointer">
                                                <input type="checkbox" name="remove_icon" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mr-2">
                                                Remove current icon
                                </label>
                        @endif
                                    </div>
                                </div>
                            </div>
                    </div>

                        <!-- Color Customization -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                    </svg>
                                </div>
                    <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Color Scheme</h3>
                                    <p class="text-sm text-gray-500">Customize your system's color palette</p>
                        </div>
                    </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                                    <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                                    <div class="flex items-center space-x-3">
                                <input type="color" name="primary_color" id="primary_color"
                                               class="h-14 w-20 border-2 border-gray-300 rounded-lg cursor-pointer shadow-sm"
                                               value="{{ $settings['primary_color'] ?? '#4F46E5' }}">
                                        <div class="flex-1">
                                            <input type="text" value="{{ $settings['primary_color'] ?? '#4F46E5' }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm" readonly>
                            </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Main color for buttons and links</p>
                        </div>

                        <div>
                                    <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-2">Secondary Color</label>
                                    <div class="flex items-center space-x-3">
                                <input type="color" name="secondary_color" id="secondary_color"
                                               class="h-14 w-20 border-2 border-gray-300 rounded-lg cursor-pointer shadow-sm"
                                               value="{{ $settings['secondary_color'] ?? '#6366F1' }}">
                                        <div class="flex-1">
                                            <input type="text" value="{{ $settings['secondary_color'] ?? '#6366F1' }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm" readonly>
                            </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Secondary color for accents</p>
                                </div>
                        </div>
                    </div>

                    <!-- Seasonal Effects -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                            <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Seasonal Effects</h3>
                                    <p class="text-sm text-gray-500">Add festive animations to your homepage</p>
                                </div>
                    </div>

                        <div>
                                <label for="seasonal_effects" class="block text-sm font-medium text-gray-700 mb-2">Enable Seasonal Effects</label>
                                    <select name="seasonal_effects" id="seasonal_effects"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="disabled" {{ ($settings['seasonal_effects'] ?? 'disabled') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                    <option value="halloween" {{ ($settings['seasonal_effects'] ?? 'disabled') == 'halloween' ? 'selected' : '' }}>🎃 Halloween (Flying Spiders & Bats)</option>
                                    <option value="christmas" {{ ($settings['seasonal_effects'] ?? 'disabled') == 'christmas' ? 'selected' : '' }}>❄️ Christmas (Falling Snow)</option>
                                    </select>
                                <p class="mt-2 text-xs text-gray-500">Add seasonal animations to the home page</p>
                                </div>
                            </div>

                        <!-- Preview Section -->
                        <div class="form-section bg-gradient-to-br from-indigo-50 to-purple-50 border-2 border-indigo-200">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-indigo-200 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                        <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Live Preview</h3>
                                    <p class="text-sm text-gray-500">See how your changes will look</p>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg p-6 border border-indigo-200">
                                <div class="flex items-center space-x-3 mb-4">
                                    @if($settings['system_logo'])
                                        <img src="{{ Storage::url($settings['system_logo']) }}" alt="Logo Preview" class="h-10 w-auto object-contain" id="preview-logo">
                                    @else
                                        <div class="h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center" id="preview-logo-placeholder">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="text-2xl font-bold text-gray-900" id="preview-name">{{ $settings['system_name'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-4" id="preview-description">{{ $settings['system_description'] }}</p>
                                <div class="flex items-center space-x-2 pt-4 border-t border-gray-200">
                                    @if(isset($settings['system_icon']) && $settings['system_icon'])
                                        <img src="{{ Storage::url($settings['system_icon']) }}" alt="Icon Preview" class="h-6 w-6 object-contain" id="preview-icon">
                                    @else
                                        <div class="h-6 w-6 bg-gray-200 rounded flex items-center justify-center" id="preview-icon-placeholder">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="text-xs text-gray-500">Favicon preview</span>
                                </div>
                            </div>
                        </div>

                        <!-- Overtime Settings -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Overtime Settings</h3>
                                    <p class="text-sm text-gray-500">Control how much overtime history is credited for employees.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="overtime_months_credited" class="block text-sm font-medium text-gray-700 mb-2">
                                        Overtime Credited Window
                                    </label>
                                    @php
                                        // Get value directly from settings array - ensure it exists
                                        $currentWindow = isset($settings['overtime_months_credited']) ? (int)$settings['overtime_months_credited'] : 12;
                                        // Override with old() only if validation error
                                        if (old('overtime_months_credited') !== null) {
                                            $currentWindow = (int) old('overtime_months_credited');
                                        }
                                    @endphp
                                    <select id="overtime_months_credited" name="overtime_months_credited"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="12" {{ $currentWindow == 12 ? 'selected' : '' }}>Current Year (12 months)</option>
                                        <option value="9" {{ $currentWindow == 9 ? 'selected' : '' }}>Last 9 months</option>
                                        <option value="6" {{ $currentWindow == 6 ? 'selected' : '' }}>Last 6 months</option>
                                        <option value="3" {{ $currentWindow == 3 ? 'selected' : '' }}>Last 3 months</option>
                                        <option value="1" {{ $currentWindow == 1 ? 'selected' : '' }}>Last 1 month</option>
                                    </select>
                                    <p class="mt-2 text-xs text-gray-500">
                                        This setting applies <strong>globally</strong> to all employees when calculating overtime balances
                                        in Leave Requests and profiles.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Request Signatories -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Leave Request Signatories</h3>
                                    <p class="text-sm text-gray-500">Configure names for leave request letter templates.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="leave_immediate_supervisor" class="block text-sm font-medium text-gray-700 mb-2">
                                        Immediate Supervisor Name
                                    </label>
                                    @php
                                        $supervisorValue = isset($settings['leave_immediate_supervisor']) ? $settings['leave_immediate_supervisor'] : 'CHARMAINE JOY ROSATACE';
                                        if (old('leave_immediate_supervisor') !== null) {
                                            $supervisorValue = old('leave_immediate_supervisor');
                                        }
                                    @endphp
                                    <input type="text" id="leave_immediate_supervisor" name="leave_immediate_supervisor"
                                           value="{{ $supervisorValue }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="mt-2 text-xs text-gray-500">
                                        Name displayed as "IMMEDIATE SUPERVISOR" in leave request letters.
                                    </p>
                                </div>
                                <div>
                                    <label for="leave_hr_admin" class="block text-sm font-medium text-gray-700 mb-2">
                                        HR Admin Name
                                    </label>
                                    @php
                                        $hrAdminValue = isset($settings['leave_hr_admin']) ? $settings['leave_hr_admin'] : 'MAY GRACE ACOSTA';
                                        if (old('leave_hr_admin') !== null) {
                                            $hrAdminValue = old('leave_hr_admin');
                                        }
                                    @endphp
                                    <input type="text" id="leave_hr_admin" name="leave_hr_admin"
                                           value="{{ $hrAdminValue }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="mt-2 text-xs text-gray-500">
                                        Name displayed as "HR ADMIN" in leave request letters.
                                    </p>
                                </div>
                                <div>
                                    <label for="leave_cto" class="block text-sm font-medium text-gray-700 mb-2">
                                        Chief Technology Officer Name
                                    </label>
                                    @php
                                        $ctoValue = isset($settings['leave_cto']) ? $settings['leave_cto'] : 'NITISH KHEMANI';
                                        if (old('leave_cto') !== null) {
                                            $ctoValue = old('leave_cto');
                                        }
                                    @endphp
                                    <input type="text" id="leave_cto" name="leave_cto"
                                           value="{{ $ctoValue }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="mt-2 text-xs text-gray-500">
                                        Name displayed as "CHIEF TECHNOLOGY OFFICER" in leave request letters.
                                    </p>
                                </div>
                                <div>
                                    <label for="leave_admin_notification_email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Admin Notification Emails
                                    </label>
                                    <div id="email-list-container" class="space-y-2 mb-2">
                                        @php
                                            $oldEmails = old('leave_admin_notification_email');
                                            if (is_array($oldEmails)) {
                                                $emailArray = array_filter(array_map('trim', $oldEmails));
                                            } else {
                                                $emails = $oldEmails ?? ($settings['leave_admin_notification_email'] ?? '');
                                                $emailArray = !empty($emails) ? explode(',', $emails) : [''];
                                                $emailArray = array_map('trim', $emailArray);
                                                $emailArray = array_filter($emailArray);
                                            }
                                            if (empty($emailArray)) {
                                                $emailArray = [''];
                                            }
                                        @endphp
                                        @foreach($emailArray as $index => $email)
                                            <div class="email-input-group flex items-center space-x-2">
                                                <input type="email"
                                                       name="leave_admin_notification_email[]"
                                                       value="{{ $email }}"
                                                       placeholder="admin@example.com"
                                                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                @if($index === 0)
                                                    <button type="button"
                                                            onclick="addEmailField()"
                                                            class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            onclick="removeEmailField(this)"
                                                            class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Email addresses to receive notifications when employees submit leave requests. You can add multiple emails. Leave empty to disable notifications.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Default Leave Balances Section -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-green-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Default Leave Balances</h3>
                                    <p class="text-sm text-gray-500">Set default vacation and sick leave balances for new employees.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="default_vacation_balance" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Default Vacation Balance (Days)
                                        </span>
                                    </label>
                                    @php
                                        // Get the value - check if it exists in settings array
                                        $vacationValue = 0;
                                        if (isset($settings['default_vacation_balance'])) {
                                            $vacationValue = (float) $settings['default_vacation_balance'];
                                        }
                                        // Override with old() only if validation error
                                        if (old('default_vacation_balance') !== null) {
                                            $vacationValue = (float) old('default_vacation_balance');
                                        }
                                    @endphp
                                    <input type="number" id="default_vacation_balance" name="default_vacation_balance"
                                           value="{{ $vacationValue }}"
                                           min="0" max="365" step="0.5"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="mt-2 text-xs text-gray-500">
                                        Default vacation leave days assigned to new employees. Can be adjusted per employee.
                                    </p>
                                </div>
                                <div>
                                    <label for="default_sick_leave_balance" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Default Sick Leave Balance (Days)
                                        </span>
                                    </label>
                                    @php
                                        // Get the value - check if it exists in settings array
                                        $sickValue = 0;
                                        if (isset($settings['default_sick_leave_balance'])) {
                                            $sickValue = (float) $settings['default_sick_leave_balance'];
                                        }
                                        // Override with old() only if validation error
                                        if (old('default_sick_leave_balance') !== null) {
                                            $sickValue = (float) old('default_sick_leave_balance');
                                        }
                                    @endphp
                                    <input type="number" id="default_sick_leave_balance" name="default_sick_leave_balance"
                                           value="{{ $sickValue }}"
                                           min="0" max="365" step="0.5"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="mt-2 text-xs text-gray-500">
                                        Default sick leave days assigned to new employees. Can be adjusted per employee.
                                    </p>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                <!-- Hiring Process Tab -->
                <div id="content-hiring" class="tab-content hidden">
                    <div class="space-y-8">
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Hiring Process Configuration</h3>
                                    <p class="text-sm text-gray-500">Manage your hiring workflow and application settings</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label for="hiring_process_enabled" class="block text-sm font-medium text-gray-700 mb-2">Enable Hiring Process</label>
                                    <select name="hiring_process_enabled" id="hiring_process_enabled"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @php
                                            $hiringProcessEnabledValue = old('hiring_process_enabled', $settings['hiring_process_enabled'] ?? 'enabled');
                                        @endphp
                                        <option value="enabled" {{ $hiringProcessEnabledValue == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                        <option value="disabled" {{ $hiringProcessEnabledValue == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="hiring_process_description" class="block text-sm font-medium text-gray-700 mb-2">Hiring Process Description</label>
                                    <textarea name="hiring_process_description" id="hiring_process_description" rows="4"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Describe your hiring process workflow...">{{ old('hiring_process_description', $settings['hiring_process_description'] ?? '') }}</textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="minimum_quiz_score" class="block text-sm font-medium text-gray-700 mb-2">Minimum Quiz Score (%)</label>
                                        <input type="number" name="minimum_quiz_score" id="minimum_quiz_score" min="0" max="100"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               value="{{ old('minimum_quiz_score', $settings['minimum_quiz_score'] ?? 70) }}">
                                        <p class="mt-2 text-xs text-gray-500">Minimum score required to pass</p>
                                    </div>

                                    <div>
                                        <label for="auto_approve_score" class="block text-sm font-medium text-gray-700 mb-2">Auto-Approve Score (%)</label>
                                        <input type="number" name="auto_approve_score" id="auto_approve_score" min="0" max="100"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               value="{{ old('auto_approve_score', $settings['auto_approve_score'] ?? '') }}">
                                        <p class="mt-2 text-xs text-gray-500">Score threshold for automatic approval</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="hiring_stages" class="block text-sm font-medium text-gray-700 mb-2">Hiring Process Stages</label>
                                    <textarea name="hiring_stages" id="hiring_stages" rows="5"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Stage 1: Application Review&#10;Stage 2: Quiz Assessment&#10;Stage 3: Technical Interview&#10;Stage 4: Final Decision">{{ old('hiring_stages', $settings['hiring_stages'] ?? '') }}</textarea>
                                    <p class="mt-2 text-xs text-gray-500">List the stages of your hiring process (one per line)</p>
                    </div>

                            <div>
                                    <label for="hiring_email_notifications" class="block text-sm font-medium text-gray-700 mb-2">Email Notifications</label>
                                    <select name="hiring_email_notifications" id="hiring_email_notifications"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @php
                                            $hiringEmailNotificationsValue = old('hiring_email_notifications', $settings['hiring_email_notifications'] ?? 'enabled');
                                        @endphp
                                        <option value="enabled" {{ $hiringEmailNotificationsValue == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                        <option value="disabled" {{ $hiringEmailNotificationsValue == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="hiring_instructions" class="block text-sm font-medium text-gray-700 mb-2">Instructions for Applicants</label>
                                    <textarea name="hiring_instructions" id="hiring_instructions" rows="4"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Instructions that will be shown to applicants...">{{ old('hiring_instructions', $settings['hiring_instructions'] ?? '') }}</textarea>
                            </div>

                                <div>
                                    <label for="hiring_application_public_access" class="block text-sm font-medium text-gray-700 mb-2">Public Application Access</label>
                                    <select name="hiring_application_public_access" id="hiring_application_public_access"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @php
                                            $hiringApplicationPublicAccessValue = old('hiring_application_public_access', $settings['hiring_application_public_access'] ?? 'disabled');
                                        @endphp
                                        <option value="disabled" {{ $hiringApplicationPublicAccessValue == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                        <option value="enabled" {{ $hiringApplicationPublicAccessValue == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="hiring_application_url" class="block text-sm font-medium text-gray-700 mb-2">Application URL Path</label>
                                    <div class="flex rounded-lg shadow-sm">
                                        <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm font-medium">
                                            {{ url('/') }}/
                                        </span>
                                        <input type="text" name="hiring_application_url" id="hiring_application_url"
                                               value="{{ old('hiring_application_url', $settings['hiring_application_url'] ?? 'hiring/apply') }}"
                                               placeholder="hiring/apply"
                                               class="flex-1 min-w-0 block w-full px-4 py-3 rounded-r-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Custom URL path for the hiring application form</p>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Email Configuration Tab -->
                <div id="content-email" class="tab-content hidden">
                    <div class="space-y-8">
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-green-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Email Configuration</h3>
                                    <p class="text-sm text-gray-500">Configure SMTP settings for email notifications</p>
                                </div>
                            </div>

                            <!-- Current Configuration Summary -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Current Email Configuration</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Mail Driver:</span>
                                        <span class="ml-2 font-medium text-gray-900">{{ ucfirst($settings['mail_mailer'] ?? 'Log') }}</span>
                                    </div>
                                    @if(!empty($settings['mail_host']))
                                    <div>
                                        <span class="text-gray-600">SMTP Host:</span>
                                        <span class="ml-2 font-medium text-gray-900">{{ $settings['mail_host'] }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($settings['mail_port']))
                                    <div>
                                        <span class="text-gray-600">SMTP Port:</span>
                                        <span class="ml-2 font-medium text-gray-900">{{ $settings['mail_port'] }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($settings['mail_encryption']))
                                    <div>
                                        <span class="text-gray-600">Encryption:</span>
                                        <span class="ml-2 font-medium text-gray-900">{{ strtoupper($settings['mail_encryption']) }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($settings['mail_from_address']))
                                    <div>
                                        <span class="text-gray-600">From Address:</span>
                                        <span class="ml-2 font-medium text-gray-900">{{ $settings['mail_from_address'] }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($settings['mail_from_name']))
                                    <div>
                                        <span class="text-gray-600">From Name:</span>
                                        <span class="ml-2 font-medium text-gray-900">{{ $settings['mail_from_name'] }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($settings['mail_username']))
                                    <div>
                                        <span class="text-gray-600">Username:</span>
                                        <span class="ml-2 font-medium text-gray-900">{{ $settings['mail_username'] }}</span>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="text-gray-600">Password:</span>
                                        <span class="ml-2 font-medium text-gray-900">
                                            {{ !empty($settings['mail_password']) ? '•••••••• (Set)' : 'Not Set' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label for="mail_mailer" class="block text-sm font-medium text-gray-700 mb-2">Mail Driver</label>
                                    @php
                                        $mailMailerValue = isset($settings['mail_mailer']) ? $settings['mail_mailer'] : 'log';
                                        if (old('mail_mailer') !== null) {
                                            $mailMailerValue = old('mail_mailer');
                                        }
                                    @endphp
                                    <select name="mail_mailer" id="mail_mailer"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="smtp" {{ $mailMailerValue == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="sendmail" {{ $mailMailerValue == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                        <option value="mailgun" {{ $mailMailerValue == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                        <option value="ses" {{ $mailMailerValue == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                        <option value="postmark" {{ $mailMailerValue == 'postmark' ? 'selected' : '' }}>Postmark</option>
                                        <option value="resend" {{ $mailMailerValue == 'resend' ? 'selected' : '' }}>Resend</option>
                                        <option value="log" {{ $mailMailerValue == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                                        <option value="array" {{ $mailMailerValue == 'array' ? 'selected' : '' }}>Array (Testing)</option>
                                    </select>
                                    <p class="mt-2 text-xs text-gray-500">Select the mail driver. Use "Log" for testing</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="mail_host" class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                                        @php
                                            $mailHostValue = isset($settings['mail_host']) ? $settings['mail_host'] : '';
                                            if (old('mail_host') !== null) {
                                                $mailHostValue = old('mail_host');
                                            }
                                        @endphp
                                        <input type="text" name="mail_host" id="mail_host"
                                               value="{{ $mailHostValue }}"
                                               placeholder="smtp.gmail.com"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <div>
                                        <label for="mail_port" class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                                        @php
                                            $mailPortValue = isset($settings['mail_port']) ? $settings['mail_port'] : '587';
                                            if (old('mail_port') !== null) {
                                                $mailPortValue = old('mail_port');
                                            }
                                        @endphp
                                        <input type="number" name="mail_port" id="mail_port"
                                               value="{{ $mailPortValue }}"
                                               placeholder="587"
                                               min="1" max="65535"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="mail_username" class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                                        @php
                                            $mailUsernameValue = isset($settings['mail_username']) ? $settings['mail_username'] : '';
                                            if (old('mail_username') !== null) {
                                                $mailUsernameValue = old('mail_username');
                                            }
                                        @endphp
                                        <input type="text" name="mail_username" id="mail_username"
                                               value="{{ $mailUsernameValue }}"
                                               placeholder="your-email@gmail.com"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <div>
                                        <label for="mail_password" class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                                        <input type="password" name="mail_password" id="mail_password"
                                               value=""
                                               placeholder="{{ !empty($settings['mail_password']) ? 'Leave blank to keep current password' : 'Enter SMTP password' }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @if(!empty($settings['mail_password']))
                                        <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password. Enter a new password to update it.</p>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label for="mail_encryption" class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                                    @php
                                        $mailEncryptionValue = isset($settings['mail_encryption']) ? $settings['mail_encryption'] : 'tls';
                                        if (old('mail_encryption') !== null) {
                                            $mailEncryptionValue = old('mail_encryption');
                                        }
                                    @endphp
                                    <select name="mail_encryption" id="mail_encryption"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="tls" {{ $mailEncryptionValue == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ $mailEncryptionValue == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="null" {{ $mailEncryptionValue == 'null' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="mail_from_address" class="block text-sm font-medium text-gray-700 mb-2">From Email Address</label>
                                        @php
                                            $mailFromAddressValue = isset($settings['mail_from_address']) ? $settings['mail_from_address'] : '';
                                            if (old('mail_from_address') !== null) {
                                                $mailFromAddressValue = old('mail_from_address');
                                            }
                                        @endphp
                                        <input type="email" name="mail_from_address" id="mail_from_address"
                                               value="{{ $mailFromAddressValue }}"
                                               placeholder="noreply@example.com"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <div>
                                        <label for="mail_from_name" class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                                        @php
                                            $mailFromNameValue = isset($settings['mail_from_name']) ? $settings['mail_from_name'] : '';
                                            if (old('mail_from_name') !== null) {
                                                $mailFromNameValue = old('mail_from_name');
                                            }
                                        @endphp
                                        <input type="text" name="mail_from_name" id="mail_from_name"
                                               value="{{ $mailFromNameValue }}"
                                               placeholder="{{ $settings['system_name'] ?? 'System' }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>

                                <div class="bg-blue-50 border-l-4 border-blue-400 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">Email Configuration Tips</h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <ul class="list-disc list-inside space-y-1">
                                                    <li>For Gmail: Use port 587 with TLS, enable "Less secure app access" or use an App Password</li>
                                                    <li>For testing: Use "Log" driver to write emails to log files instead of sending</li>
                                                    <li>After changing settings, test by sending a test email</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Test Email Button -->
                                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Test Email Configuration</h4>
                                        <p class="text-xs text-gray-600 mb-4">Send a test email to verify your email settings are working correctly</p>

                                        <div class="flex items-end space-x-3">
                                            <div class="flex-1">
                                                <label for="test_email_address" class="block text-xs font-medium text-gray-700 mb-2">Test Email Address</label>
                                                <input type="email" id="test_email_address"
                                                       value="{{ auth()->user()->email ?? '' }}"
                                                       placeholder="Enter email address to test"
                                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            </div>
                                            <button type="button" id="test-email-btn" onclick="sendTestEmail()"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 whitespace-nowrap">
                                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                <span id="test-email-btn-text">Send Test Email</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="test-email-result" class="mt-4 hidden"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Tab -->
                <div id="content-contact" class="tab-content hidden">
                    <div class="p-6">
                        <div class="space-y-6">
                            <!-- Contact Information Section -->
                            <div class="form-section">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-2">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                                        <p class="text-sm text-gray-500">Configure contact details displayed on the landing page</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <!-- Email Contact -->
                                    <div>
                                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                        <input type="email" name="contact_email" id="contact_email"
                                               value="{{ old('contact_email', $settings['contact_email'] ?? 'support@quizsystem.com') }}"
                                               placeholder="support@example.com"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <p class="mt-1 text-xs text-gray-500">Email address displayed on the contact page</p>
                                    </div>

                                    <div>
                                        <label for="contact_email_response_time" class="block text-sm font-medium text-gray-700 mb-2">Email Response Time Message</label>
                                        <input type="text" name="contact_email_response_time" id="contact_email_response_time"
                                               value="{{ old('contact_email_response_time', $settings['contact_email_response_time'] ?? 'We typically respond within 24 hours') }}"
                                               placeholder="We typically respond within 24 hours"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <!-- Phone Contact -->
                                    <div>
                                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                        <input type="text" name="contact_phone" id="contact_phone"
                                               value="{{ old('contact_phone', $settings['contact_phone'] ?? '+1 (555) 123-4567') }}"
                                               placeholder="+1 (555) 123-4567"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <div>
                                        <label for="contact_phone_hours" class="block text-sm font-medium text-gray-700 mb-2">Phone Support Hours</label>
                                        <input type="text" name="contact_phone_hours" id="contact_phone_hours"
                                               value="{{ old('contact_phone_hours', $settings['contact_phone_hours'] ?? 'Monday - Friday, 9 AM - 6 PM EST') }}"
                                               placeholder="Monday - Friday, 9 AM - 6 PM EST"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <!-- Live Chat -->
                                    <div>
                                        <label for="contact_live_chat_description" class="block text-sm font-medium text-gray-700 mb-2">Live Chat Description</label>
                                        <input type="text" name="contact_live_chat_description" id="contact_live_chat_description"
                                               value="{{ old('contact_live_chat_description', $settings['contact_live_chat_description'] ?? 'Available on our platform') }}"
                                               placeholder="Available on our platform"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <div>
                                        <label for="contact_live_chat_hours" class="block text-sm font-medium text-gray-700 mb-2">Live Chat Hours Description</label>
                                        <input type="text" name="contact_live_chat_hours" id="contact_live_chat_hours"
                                               value="{{ old('contact_live_chat_hours', $settings['contact_live_chat_hours'] ?? 'Get instant help while using the system') }}"
                                               placeholder="Get instant help while using the system"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <!-- FAQ -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="contact_faq_url" class="block text-sm font-medium text-gray-700 mb-2">FAQ URL</label>
                                            <input type="text" name="contact_faq_url" id="contact_faq_url"
                                                   value="{{ old('contact_faq_url', $settings['contact_faq_url'] ?? '#') }}"
                                                   placeholder="# or /faq"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <div>
                                            <label for="contact_faq_text" class="block text-sm font-medium text-gray-700 mb-2">FAQ Link Text</label>
                                            <input type="text" name="contact_faq_text" id="contact_faq_text"
                                                   value="{{ old('contact_faq_text', $settings['contact_faq_text'] ?? 'View FAQ →') }}"
                                                   placeholder="View FAQ →"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Support Hours Section -->
                            <div class="form-section">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-2">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Support Hours</h3>
                                        <p class="text-sm text-gray-500">Configure support hours displayed on the contact page</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <!-- Email Support Hours -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="contact_email_support_hours" class="block text-sm font-medium text-gray-700 mb-2">Email Support Hours</label>
                                            <input type="text" name="contact_email_support_hours" id="contact_email_support_hours"
                                                   value="{{ old('contact_email_support_hours', $settings['contact_email_support_hours'] ?? '24/7 Available') }}"
                                                   placeholder="24/7 Available"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <div>
                                            <label for="contact_email_support_response" class="block text-sm font-medium text-gray-700 mb-2">Email Response Time</label>
                                            <input type="text" name="contact_email_support_response" id="contact_email_support_response"
                                                   value="{{ old('contact_email_support_response', $settings['contact_email_support_response'] ?? 'Response within 24 hours') }}"
                                                   placeholder="Response within 24 hours"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>

                                    <!-- Phone Support Hours -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="contact_phone_support_days" class="block text-sm font-medium text-gray-700 mb-2">Phone Support Days</label>
                                            <input type="text" name="contact_phone_support_days" id="contact_phone_support_days"
                                                   value="{{ old('contact_phone_support_days', $settings['contact_phone_support_days'] ?? 'Monday - Friday') }}"
                                                   placeholder="Monday - Friday"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <div>
                                            <label for="contact_phone_support_time" class="block text-sm font-medium text-gray-700 mb-2">Phone Support Time</label>
                                            <input type="text" name="contact_phone_support_time" id="contact_phone_support_time"
                                                   value="{{ old('contact_phone_support_time', $settings['contact_phone_support_time'] ?? '9:00 AM - 6:00 PM EST') }}"
                                                   placeholder="9:00 AM - 6:00 PM EST"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>

                                    <!-- Live Chat Support Hours -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="contact_live_chat_days" class="block text-sm font-medium text-gray-700 mb-2">Live Chat Support Days</label>
                                            <input type="text" name="contact_live_chat_days" id="contact_live_chat_days"
                                                   value="{{ old('contact_live_chat_days', $settings['contact_live_chat_days'] ?? 'Monday - Friday') }}"
                                                   placeholder="Monday - Friday"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <div>
                                            <label for="contact_live_chat_time" class="block text-sm font-medium text-gray-700 mb-2">Live Chat Support Time</label>
                                            <input type="text" name="contact_live_chat_time" id="contact_live_chat_time"
                                                   value="{{ old('contact_live_chat_time', $settings['contact_live_chat_time'] ?? '10:00 AM - 5:00 PM EST') }}"
                                                   placeholder="10:00 AM - 5:00 PM EST"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health Tab -->
                <div id="content-health" class="tab-content hidden">
                    <div class="space-y-8">
                        <!-- Overall Status -->
                        <div class="form-section {{ $health['status'] === 'healthy' ? 'bg-gradient-to-br from-green-50 to-emerald-50 border-green-200' : ($health['status'] === 'warning' ? 'bg-gradient-to-br from-yellow-50 to-amber-50 border-yellow-200' : 'bg-gradient-to-br from-red-50 to-rose-50 border-red-200') }}">
                            <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 {{ $health['status'] === 'healthy' ? 'bg-green-100' : ($health['status'] === 'warning' ? 'bg-yellow-100' : 'bg-red-100') }} rounded-lg p-3">
                                        @if($health['status'] === 'healthy')
                                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @elseif($health['status'] === 'warning')
                                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                @else
                                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold {{ $health['status'] === 'healthy' ? 'text-green-900' : ($health['status'] === 'warning' ? 'text-yellow-900' : 'text-red-900') }}">
                                            System Status: {{ ucfirst($health['status']) }}
                                        </h3>
                                        <p class="text-sm {{ $health['status'] === 'healthy' ? 'text-green-700' : ($health['status'] === 'warning' ? 'text-yellow-700' : 'text-red-700') }} mt-1">
                                            @if($health['status'] === 'healthy')
                                                All systems are operating normally
                                            @elseif($health['status'] === 'warning')
                                                Some systems may need attention
                                            @else
                                                Critical issues detected
                                @endif
                                        </p>
                            </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Last Checked</p>
                                    <p class="text-sm font-semibold text-gray-700" id="last-checked-time">{{ now()->format('H:i:s') }}</p>
                                    <button onclick="refreshHealth()" class="mt-2 text-xs text-indigo-600 hover:text-indigo-800 flex items-center space-x-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <span>Refresh</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- System Checks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-white rounded-xl border-2 {{ $health['checks']['database'] ?? false ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-700">Database</h4>
                                    @if($health['checks']['database'] ?? false)
                                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                    @endif
                                        </div>
                                <p class="text-xs text-gray-600">{{ $health['database']['status'] ?? 'Unknown' }}</p>
                                @if(isset($health['database']['driver']))
                                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($health['database']['driver']) }}</p>
                                    @endif
                                </div>

                            <div class="bg-white rounded-xl border-2 {{ $health['checks']['cache'] ?? false ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-700">Cache</h4>
                                    @if($health['checks']['cache'] ?? false)
                                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                            </div>
                                <p class="text-xs text-gray-600">{{ $health['checks']['cache'] ?? false ? 'Operational' : 'Failed' }}</p>
                                @if(isset($health['application']['cache_driver']))
                                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($health['application']['cache_driver']) }}</p>
                                @endif
                        </div>

                            <div class="bg-white rounded-xl border-2 {{ $health['checks']['storage'] ?? false ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-700">Storage</h4>
                                    @if($health['checks']['storage'] ?? false)
                                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                    </div>
                                <p class="text-xs text-gray-600">{{ $health['checks']['storage'] ?? false ? 'Accessible' : 'Failed' }}</p>
                                <p class="text-xs text-gray-500 mt-1">Public Disk</p>
                </div>

                            <div class="bg-white rounded-xl border-2 {{ $health['checks']['queue'] ?? false ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50' }} p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-700">Queue</h4>
                                    @if($health['checks']['queue'] ?? false)
                                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600">{{ $health['checks']['queue'] ?? false ? 'Operational' : 'Warning' }}</p>
                                @if(isset($health['application']['queue_driver']))
                                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($health['application']['queue_driver']) }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Server Information -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                    </svg>
                                </div>
                        <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Server Information</h3>
                                    <p class="text-sm text-gray-500">PHP and server configuration details</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">PHP Version</p>
                                    <p class="text-lg font-semibold text-gray-900" id="php-version">{{ $health['server']['php_version'] ?? 'Unknown' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Memory Limit</p>
                                    <p class="text-lg font-semibold text-gray-900" id="memory-limit">{{ $health['server']['php_memory_limit'] ?? 'Unknown' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Memory Usage</p>
                                    <p class="text-lg font-semibold text-gray-900" id="memory-usage">{{ $health['server']['memory_usage'] ?? 'Unknown' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Peak Memory</p>
                                    <p class="text-lg font-semibold text-gray-900" id="memory-peak">{{ $health['server']['memory_peak'] ?? 'Unknown' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Max Execution Time</p>
                                    <p class="text-lg font-semibold text-gray-900" id="exec-time">{{ $health['server']['php_max_execution_time'] ?? 'Unknown' }}s</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Upload Max Size</p>
                                    <p class="text-lg font-semibold text-gray-900" id="upload-size">{{ $health['server']['php_upload_max_filesize'] ?? 'Unknown' }}</p>
                                </div>
                                @if(isset($health['server']['disk_total']))
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Disk Space</p>
                                        <p class="text-lg font-semibold text-gray-900" id="disk-space">{{ $health['server']['disk_free'] ?? 'Unknown' }} / {{ $health['server']['disk_total'] ?? 'Unknown' }}</p>
                                        @if(isset($health['server']['disk_used_percent']))
                                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                                <div class="disk-progress-bar bg-{{ $health['server']['disk_used_percent'] > 90 ? 'red' : ($health['server']['disk_used_percent'] > 70 ? 'yellow' : 'green') }}-600 h-2 rounded-full" style="width: {{ $health['server']['disk_used_percent'] }}%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1" id="disk-percent">{{ $health['server']['disk_used_percent'] }}% used</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Application Information -->
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Application Information</h3>
                                    <p class="text-sm text-gray-500">Laravel and application configuration</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Laravel Version</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $health['application']['laravel_version'] ?? 'Unknown' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">App Name</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $health['application']['app_name'] ?? 'Unknown' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Environment</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        <span class="px-2 py-1 rounded text-xs {{ $health['application']['app_env'] === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ strtoupper($health['application']['app_env'] ?? 'Unknown') }}
                                        </span>
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Debug Mode</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        <span class="px-2 py-1 rounded text-xs {{ $health['application']['app_debug'] === 'Enabled' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $health['application']['app_debug'] ?? 'Unknown' }}
                                        </span>
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Timezone</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $health['application']['timezone'] ?? 'Unknown' }}</p>
                                </div>
                                @if(isset($health['database']['version']))
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Database Version</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ Str::limit($health['database']['version'], 30) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Statistics -->
                        @if(isset($health['statistics']) && count($health['statistics']) > 0)
                            <div class="form-section">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2">
                                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-lg font-semibold text-gray-900">System Statistics</h3>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 animate-pulse">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                                Live
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500">Current system usage and activity (updates every 30 seconds)</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                                        <p class="text-xs font-medium text-blue-600 uppercase mb-1">Total Users</p>
                                        <p class="text-2xl font-bold text-blue-900" id="stat-total-users" data-value="{{ $health['statistics']['total_users'] ?? 0 }}">{{ number_format($health['statistics']['total_users'] ?? 0) }}</p>
                                    </div>
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                        <p class="text-xs font-medium text-green-600 uppercase mb-1">Active Users</p>
                                        <p class="text-2xl font-bold text-green-900" id="stat-active-users" data-value="{{ $health['statistics']['active_users'] ?? 0 }}">{{ number_format($health['statistics']['active_users'] ?? 0) }}</p>
                                    </div>
                                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                                        <p class="text-xs font-medium text-purple-600 uppercase mb-1">Total Quizzes</p>
                                        <p class="text-2xl font-bold text-purple-900" id="stat-total-quizzes" data-value="{{ $health['statistics']['total_quizzes'] ?? 0 }}">{{ number_format($health['statistics']['total_quizzes'] ?? 0) }}</p>
                                    </div>
                                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200">
                                        <p class="text-xs font-medium text-indigo-600 uppercase mb-1">Active Quizzes</p>
                                        <p class="text-2xl font-bold text-indigo-900" id="stat-active-quizzes" data-value="{{ $health['statistics']['active_quizzes'] ?? 0 }}">{{ number_format($health['statistics']['active_quizzes'] ?? 0) }}</p>
                                    </div>
                                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
                                        <p class="text-xs font-medium text-yellow-600 uppercase mb-1">Quiz Attempts</p>
                                        <p class="text-2xl font-bold text-yellow-900" id="stat-total-attempts" data-value="{{ $health['statistics']['total_attempts'] ?? 0 }}">{{ number_format($health['statistics']['total_attempts'] ?? 0) }}</p>
                                    </div>
                                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                                        <p class="text-xs font-medium text-orange-600 uppercase mb-1">Pending Apps</p>
                                        <p class="text-2xl font-bold text-orange-900" id="stat-pending-apps" data-value="{{ $health['statistics']['pending_applications'] ?? 0 }}">{{ number_format($health['statistics']['pending_applications'] ?? 0) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Maintenance Tab -->
                <div id="content-maintenance" class="tab-content hidden">
                    <div class="space-y-8">
                        <div class="form-section">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="flex-shrink-0 bg-red-100 rounded-lg p-2">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                        <div>
                                    <h3 class="text-lg font-semibold text-gray-900">System Maintenance</h3>
                                    <p class="text-sm text-gray-500">Control system availability and maintenance mode</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label for="maintenance_mode" class="block text-sm font-medium text-gray-700 mb-2">Maintenance Mode</label>
                                <select name="maintenance_mode" id="maintenance_mode"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="disabled" {{ ($settings['maintenance_mode'] ?? 'disabled') == 'disabled' ? 'selected' : '' }}>System Online</option>
                                        <option value="enabled" {{ ($settings['maintenance_mode'] ?? 'disabled') == 'enabled' ? 'selected' : '' }}>System Under Maintenance</option>
                                </select>
                                    <p class="mt-2 text-xs text-gray-500">When enabled, users will see a maintenance page</p>
                        </div>

                        <div>
                                    <label for="maintenance_message" class="block text-sm font-medium text-gray-700 mb-2">Maintenance Message</label>
                                <textarea name="maintenance_message" id="maintenance_message" rows="3"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="We are currently performing scheduled maintenance. Please check back later.">{{ $settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. Please check back later.' }}</textarea>
                                    <p class="mt-2 text-xs text-gray-500">Custom message displayed to users during maintenance</p>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Settings
                    </button>
                </div>
                </div>
            </form>
    </div>
</div>

<script>
// showTab is already defined in @section('scripts') above
// Just verify it exists and add any additional initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - checking showTab:', typeof window.showTab);
    if (!window.showTab) {
        console.error('showTab function still not found after DOMContentLoaded!');
        // Fallback definition
        window.showTab = function(tabName) {
            console.log('showTab fallback called for:', tabName);
            const content = document.getElementById('content-' + tabName);
            const tab = document.getElementById('tab-' + tabName);
            if (content) {
                document.querySelectorAll('.tab-content').forEach(el => {
                    el.classList.add('hidden');
                    el.style.display = 'none';
                });
                content.classList.remove('hidden');
                content.style.display = '';
            }
            if (tab) {
                document.querySelectorAll('.settings-tab').forEach(el => el.classList.remove('active'));
                tab.classList.add('active');
            }
        };
    }

// Use event delegation on the nav element - works even if buttons are added dynamically
function initTabListeners() {
    const navElement = document.querySelector('nav[aria-label="Tabs"]');
    if (navElement) {
        // Check if listener already exists
        if (navElement.hasAttribute('data-tab-listener')) {
            return; // Already initialized
        }
        navElement.setAttribute('data-tab-listener', 'true');

        // Add event delegation
        navElement.addEventListener('click', function(e) {
            const button = e.target.closest('[data-tab]');
            if (button) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const tabName = button.getAttribute('data-tab');
                console.log('Tab clicked via delegation:', tabName);
                if (tabName && window.showTab) {
                    window.showTab(tabName);
                }
                return false;
            }
        }, true); // Use capture phase to catch events early
        console.log('Tab event delegation initialized');
    } else {
        console.error('Nav element not found');
        // Fallback: try direct listeners
        setTimeout(function() {
            document.querySelectorAll('[data-tab]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const tabName = this.getAttribute('data-tab');
                    console.log('Tab clicked (fallback):', tabName);
                    if (tabName && window.showTab) {
                        window.showTab(tabName);
                    }
                    return false;
                }, true);
            });
            console.log('Fallback tab listeners initialized');
        }, 200);
    }
}

        // Try multiple times to ensure it works
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTabListeners);
        } else {
            // DOM is already ready
            initTabListeners();
        }

        // Also try after a short delay as fallback
        setTimeout(initTabListeners, 100);
    }); // Close DOMContentLoaded function

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired');

    // Update preview when form fields change
    const systemNameInput = document.getElementById('system_name');
    const systemDescriptionInput = document.getElementById('system_description');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');

    if (systemNameInput && previewName) {
    systemNameInput.addEventListener('input', function() {
        previewName.textContent = this.value;
    });
    }

    if (systemDescriptionInput && previewDescription) {
    systemDescriptionInput.addEventListener('input', function() {
        previewDescription.textContent = this.value;
    });
    }

    // Logo preview
    const logoInput = document.getElementById('system_logo');
    if (logoInput) {
    logoInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                    const preview = document.getElementById('preview-logo');
                    const placeholder = document.getElementById('preview-logo-placeholder');
                if (preview) {
                    preview.src = e.target.result;
                    } else if (placeholder) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Logo Preview';
                        img.className = 'h-10 w-auto object-contain';
                        img.id = 'preview-logo';
                        placeholder.parentNode.replaceChild(img, placeholder);
                }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    }

    // Icon preview
    const iconInput = document.getElementById('system_icon');
    if (iconInput) {
    iconInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewIcon = document.getElementById('preview-icon');
                const previewPlaceholder = document.getElementById('preview-icon-placeholder');

                if (previewIcon) {
                    previewIcon.src = e.target.result;
                } else if (previewPlaceholder) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Icon Preview';
                    img.className = 'h-6 w-6 object-contain';
                    img.id = 'preview-icon';
                    previewPlaceholder.parentNode.replaceChild(img, previewPlaceholder);
                }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    }

    // Color picker sync
    const primaryColorInput = document.getElementById('primary_color');
    if (primaryColorInput) {
        primaryColorInput.addEventListener('input', function() {
            const textInput = this.nextElementSibling?.querySelector('input[type="text"]');
            if (textInput) {
                textInput.value = this.value;
            }
        });
    }

    const secondaryColorInput = document.getElementById('secondary_color');
    if (secondaryColorInput) {
        secondaryColorInput.addEventListener('input', function() {
            const textInput = this.nextElementSibling?.querySelector('input[type="text"]');
            if (textInput) {
                textInput.value = this.value;
            }
        });
    }

    // Live Statistics Auto-Refresh
    let healthRefreshInterval;
    let isRefreshing = false;

    function startLiveStats() {
        // Refresh every 30 seconds
        healthRefreshInterval = setInterval(function() {
            if (!isRefreshing && document.getElementById('content-health') && !document.getElementById('content-health').classList.contains('hidden')) {
                refreshHealth();
            }
        }, 30000); // 30 seconds
    }

    function refreshHealth() {
        if (isRefreshing) return;

        isRefreshing = true;
        const refreshBtn = document.querySelector('button[onclick="refreshHealth()"]');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        }

        fetch('{{ route("admin.settings.health") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            updateHealthDisplay(data);
            isRefreshing = false;
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><span>Refresh</span>';
            }
        })
        .catch(error => {
            console.error('Error refreshing health:', error);
            isRefreshing = false;
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><span>Refresh</span>';
            }
        });
    }

    function updateHealthDisplay(data) {
        // Update last checked time
        const now = new Date();
        const timeElement = document.getElementById('last-checked-time');
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString();
        }

        // Update status
        const statusSection = document.querySelector('#content-health .form-section');
        if (statusSection && data.status) {
            statusSection.className = 'form-section ' +
                (data.status === 'healthy' ? 'bg-gradient-to-br from-green-50 to-emerald-50 border-green-200' :
                 data.status === 'warning' ? 'bg-gradient-to-br from-yellow-50 to-amber-50 border-yellow-200' :
                 'bg-gradient-to-br from-red-50 to-rose-50 border-red-200');

            const statusText = statusSection.querySelector('h3');
            if (statusText) {
                statusText.textContent = 'System Status: ' + data.status.charAt(0).toUpperCase() + data.status.slice(1);
                statusText.className = 'text-2xl font-bold ' +
                    (data.status === 'healthy' ? 'text-green-900' :
                     data.status === 'warning' ? 'text-yellow-900' : 'text-red-900');
            }

            const statusDesc = statusSection.querySelector('p.text-sm');
            if (statusDesc) {
                statusDesc.textContent = data.status === 'healthy' ? 'All systems are operating normally' :
                                        data.status === 'warning' ? 'Some systems may need attention' :
                                        'Critical issues detected';
                statusDesc.className = 'text-sm mt-1 ' +
                    (data.status === 'healthy' ? 'text-green-700' :
                     data.status === 'warning' ? 'text-yellow-700' : 'text-red-700');
            }
        }

        // Update service check cards
        updateServiceCard('database', data.checks?.database, data.database?.status, data.database?.driver);
        updateServiceCard('cache', data.checks?.cache, data.checks?.cache ? 'Operational' : 'Failed', data.application?.cache_driver);
        updateServiceCard('storage', data.checks?.storage, data.checks?.storage ? 'Accessible' : 'Failed', 'Public Disk');
        updateServiceCard('queue', data.checks?.queue, data.checks?.queue ? 'Operational' : 'Warning', data.application?.queue_driver);

        // Update statistics with animation
        if (data.statistics) {
            animateCounter('stat-total-users', data.statistics.total_users || 0);
            animateCounter('stat-active-users', data.statistics.active_users || 0);
            animateCounter('stat-total-quizzes', data.statistics.total_quizzes || 0);
            animateCounter('stat-active-quizzes', data.statistics.active_quizzes || 0);
            animateCounter('stat-total-attempts', data.statistics.total_attempts || 0);
            animateCounter('stat-pending-apps', data.statistics.pending_applications || 0);
        }

        // Update server info
        if (data.server) {
            updateElement('php-version', data.server.php_version);
            updateElement('memory-limit', data.server.php_memory_limit);
            updateElement('memory-usage', data.server.memory_usage);
            updateElement('memory-peak', data.server.memory_peak);
            if (data.server.php_max_execution_time) {
                updateElement('exec-time', data.server.php_max_execution_time + 's');
            }
            updateElement('upload-size', data.server.php_upload_max_filesize);

            if (data.server.disk_total) {
                updateElement('disk-space', (data.server.disk_free || 'Unknown') + ' / ' + data.server.disk_total);
            }

            if (data.server.disk_used_percent !== undefined) {
                const progressBar = document.querySelector('.disk-progress-bar');
                const diskPercent = document.getElementById('disk-percent');
                if (progressBar) {
                    const color = data.server.disk_used_percent > 90 ? 'red' :
                                  data.server.disk_used_percent > 70 ? 'yellow' : 'green';
                    progressBar.className = 'bg-' + color + '-600 h-2 rounded-full disk-progress-bar';
                    progressBar.style.width = data.server.disk_used_percent + '%';
                }
                if (diskPercent) {
                    diskPercent.textContent = data.server.disk_used_percent + '% used';
                }
            }
        }
    }

    function updateServiceCard(serviceName, isHealthy, status, driver) {
        const cards = document.querySelectorAll('.bg-white.rounded-xl');
        cards.forEach(card => {
            const title = card.querySelector('h4');
            if (title && title.textContent.toLowerCase().includes(serviceName)) {
                const isOk = isHealthy !== false;
                card.className = 'bg-white rounded-xl border-2 ' +
                    (isOk ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50') + ' p-6';

                const icon = card.querySelector('svg');
                if (icon) {
                    icon.className = 'h-5 w-5 ' + (isOk ? 'text-green-600' : 'text-red-600');
                    icon.innerHTML = isOk ?
                        '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' :
                        '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>';
                }

                const statusText = card.querySelector('p.text-xs.text-gray-600');
                if (statusText && status) {
                    statusText.textContent = status;
                }

                const driverText = card.querySelectorAll('p.text-xs.text-gray-500')[1];
                if (driverText && driver) {
                    driverText.textContent = driver ? (driver.charAt(0).toUpperCase() + driver.slice(1)) : '';
                }
            }
        });
    }

    function animateCounter(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const currentValue = parseInt(element.getAttribute('data-value') || element.textContent.replace(/,/g, '')) || 0;
        const target = parseInt(targetValue) || 0;

        if (currentValue === target) return;

        element.setAttribute('data-value', target);

        const duration = 1000; // 1 second
        const startTime = Date.now();
        const startValue = currentValue;

        function updateCounter() {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(startValue + (target - startValue) * easeOut);

            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target.toLocaleString();
            }
        }

        updateCounter();
    }

    function updateElement(id, value) {
        const element = document.getElementById(id);
        if (element && value) {
            element.textContent = value;
        }
    }

    // Enhance showTab to start/stop live stats (only if showTab exists)
    if (typeof window.showTab === 'function') {
        const originalShowTab = window.showTab;
        window.showTab = function(tabName) {
            // Call the original showTab function
            if (originalShowTab) {
                originalShowTab(tabName);
            }

            // Additional functionality for health tab
            if (tabName === 'health') {
                startLiveStats();
            } else {
                if (healthRefreshInterval) {
                    clearInterval(healthRefreshInterval);
                }
            }
        };
    }

    // Start if health tab is already active
    if (document.getElementById('tab-health')?.classList.contains('active')) {
        startLiveStats();
    }

    // Test Email Function
    window.sendTestEmail = function() {
        const emailInput = document.getElementById('test_email_address');
        const testEmailBtn = document.getElementById('test-email-btn');
        const testEmailBtnText = document.getElementById('test-email-btn-text');
        const resultDiv = document.getElementById('test-email-result');

        if (!emailInput || !emailInput.value) {
            alert('Please enter an email address to test');
            return;
        }

        const email = emailInput.value.trim();

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }

        // Disable button and show loading state
        testEmailBtn.disabled = true;
        testEmailBtn.classList.add('opacity-50', 'cursor-not-allowed');
        testEmailBtnText.innerHTML = '<svg class="h-4 w-4 mr-2 animate-spin inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending...';

        // Hide previous result
        resultDiv.classList.add('hidden');

        fetch('{{ route("admin.settings.test-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || ''
            },
            body: JSON.stringify({
                test_email: email
            })
        })
        .then(response => response.json())
        .then(data => {
            // Re-enable button
            testEmailBtn.disabled = false;
            testEmailBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            testEmailBtnText.textContent = 'Send Test Email';

            // Show result
            resultDiv.classList.remove('hidden');

            if (data.success) {
                resultDiv.className = 'mt-4 p-4 rounded-lg bg-green-50 border border-green-200';
                resultDiv.innerHTML = `
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-green-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-green-800">Test Email Sent Successfully!</h4>
                            <p class="text-sm text-green-700 mt-1">${data.message}</p>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
                resultDiv.innerHTML = `
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-red-800">Failed to Send Test Email</h4>
                            <p class="text-sm text-red-700 mt-1">${data.message}</p>
                            <p class="text-xs text-red-600 mt-2">Please check your email configuration settings and try again.</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            // Re-enable button
            testEmailBtn.disabled = false;
            testEmailBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            testEmailBtnText.textContent = 'Send Test Email';

            // Show error
            resultDiv.classList.remove('hidden');
            resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
            resultDiv.innerHTML = `
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-red-800">Error</h4>
                        <p class="text-sm text-red-700 mt-1">An error occurred while sending the test email. Please try again.</p>
                    </div>
                </div>
            `;

            console.error('Test email error:', error);
        });
    });
</script>

<script>
    // Add email field - must be in global scope for onclick handlers
    function addEmailField() {
        const container = document.getElementById('email-list-container');
        if (!container) {
            console.error('Email list container not found');
            return;
        }
        const newField = document.createElement('div');
        newField.className = 'email-input-group flex items-center space-x-2';
        newField.innerHTML = `
            <input type="email"
                   name="leave_admin_notification_email[]"
                   value=""
                   placeholder="admin@example.com"
                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <button type="button"
                    onclick="removeEmailField(this)"
                    class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        container.appendChild(newField);
    }

    // Remove email field - must be in global scope for onclick handlers
    function removeEmailField(button) {
        const container = document.getElementById('email-list-container');
        if (!container) {
            console.error('Email list container not found');
            return;
        }
        const groups = container.querySelectorAll('.email-input-group');
        if (groups.length > 1) {
            button.closest('.email-input-group').remove();
        } else {
            alert('You must have at least one email field.');
        }
    }
</script>
@endsection
