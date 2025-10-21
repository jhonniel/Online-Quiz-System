@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h1 class="text-2xl font-bold text-white">System Settings</h1>
                <p class="text-indigo-100">Customize your quiz system appearance and branding</p>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-8">

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="space-y-8">
                    <!-- System Name -->
                    <div>
                        <label for="system_name" class="block text-sm font-medium text-gray-700">System Name</label>
                        <div class="mt-1">
                            <input type="text" name="system_name" id="system_name" required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   value="{{ $settings['system_name'] }}">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">This will be displayed in the navigation and page titles.</p>
                    </div>

                    <!-- System Logo -->
                    <div>
                        <label for="system_logo" class="block text-sm font-medium text-gray-700">System Logo</label>
                        <div class="mt-1">
                            @if($settings['system_logo'])
                                <div class="mb-4">
                                    <img src="{{ Storage::url($settings['system_logo']) }}"
                                         alt="Current Logo"
                                         class="h-16 w-auto object-contain">
                                    <p class="mt-2 text-sm text-gray-500">Current logo</p>
                                </div>
                            @endif

                            <input type="file" name="system_logo" id="system_logo" accept="image/*"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Upload a new logo (PNG, JPG, GIF, SVG - Max 2MB)</p>

                        @if($settings['system_logo'])
                            <div class="mt-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remove_logo" value="1"
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-red-600">Remove current logo</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <!-- System Icon -->
                    <div>
                        <label for="system_icon" class="block text-sm font-medium text-gray-700">System Icon (Favicon)</label>
                        <div class="mt-1">
                            @if(isset($settings['system_icon']) && $settings['system_icon'])
                                <div class="mb-4">
                                    <img src="{{ Storage::url($settings['system_icon']) }}"
                                         alt="Current Icon"
                                         class="h-8 w-8 object-contain">
                                    <p class="mt-2 text-sm text-gray-500">Current icon (favicon)</p>
                                </div>
                            @endif

                            <input type="file" name="system_icon" id="system_icon" accept="image/*"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Upload a new icon/favicon (PNG, JPG, GIF, SVG - Max 1MB). Recommended size: 32x32px or 64x64px</p>

                        @if(isset($settings['system_icon']) && $settings['system_icon'])
                            <div class="mt-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remove_icon" value="1"
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-red-600">Remove current icon</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <!-- System Description -->
                    <div>
                        <label for="system_description" class="block text-sm font-medium text-gray-700">System Description</label>
                        <div class="mt-1">
                            <textarea name="system_description" id="system_description" rows="3"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ $settings['system_description'] }}</textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Brief description of your quiz system.</p>
                    </div>

                    <!-- Color Settings -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="primary_color" class="block text-sm font-medium text-gray-700">Primary Color</label>
                            <div class="mt-1">
                                <input type="color" name="primary_color" id="primary_color"
                                       class="h-10 w-full border border-gray-300 rounded-md"
                                       value="{{ $settings['primary_color'] }}">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Main color for buttons and links.</p>
                        </div>

                        <div>
                            <label for="secondary_color" class="block text-sm font-medium text-gray-700">Secondary Color</label>
                            <div class="mt-1">
                                <input type="color" name="secondary_color" id="secondary_color"
                                       class="h-10 w-full border border-gray-300 rounded-md"
                                       value="{{ $settings['secondary_color'] }}">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Secondary color for text and borders.</p>
                        </div>
                    </div>

                    <!-- Seasonal Effects -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Seasonal Effects</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="seasonal_effects" class="block text-sm font-medium text-gray-700">Enable Seasonal Effects</label>
                                <div class="mt-1">
                                    <select name="seasonal_effects" id="seasonal_effects"
                                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="disabled" {{ ($settings['seasonal_effects'] ?? 'disabled') == 'disabled' ? 'selected' : '' }}>
                                            Disabled
                                        </option>
                                        <option value="halloween" {{ ($settings['seasonal_effects'] ?? 'disabled') == 'halloween' ? 'selected' : '' }}>
                                            Halloween (Flying Spiders & Bats)
                                        </option>
                                        <option value="christmas" {{ ($settings['seasonal_effects'] ?? 'disabled') == 'christmas' ? 'selected' : '' }}>
                                            Christmas (Falling Snow)
                                        </option>
                                    </select>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Add seasonal animations to the home page. Halloween shows flying spiders and bats, Christmas shows falling snow.</p>
                            </div>

                            <!-- Effect Previews -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">🎃 Halloween Effect</h4>
                                    <p class="text-xs text-gray-600">Flying spiders and bats across the screen</p>
                                </div>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">❄️ Christmas Effect</h4>
                                    <p class="text-xs text-gray-600">Gentle falling snow animation</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Preview</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center space-x-3">
                                @if($settings['system_logo'])
                                    <img src="{{ Storage::url($settings['system_logo']) }}"
                                         alt="Logo Preview"
                                         class="h-8 w-auto object-contain">
                                @else
                                    <div class="h-8 w-8 bg-gray-300 rounded flex items-center justify-center">
                                        <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="text-xl font-bold" id="preview-name">{{ $settings['system_name'] }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600" id="preview-description">{{ $settings['system_description'] }}</p>

                            <!-- Icon Preview -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Icon Preview (Favicon)</h4>
                                <div class="flex items-center space-x-2">
                                    @if(isset($settings['system_icon']) && $settings['system_icon'])
                                        <img src="{{ Storage::url($settings['system_icon']) }}"
                                             alt="Icon Preview"
                                             class="h-6 w-6 object-contain"
                                             id="preview-icon">
                                    @else
                                        <div class="h-6 w-6 bg-gray-300 rounded flex items-center justify-center" id="preview-icon-placeholder">
                                            <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="text-sm text-gray-500">This will appear in browser tabs and bookmarks</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Maintenance -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Maintenance</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="maintenance_mode" class="block text-sm font-medium text-gray-700">Maintenance Mode</label>
                            <div class="mt-1">
                                <select name="maintenance_mode" id="maintenance_mode"
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="disabled" {{ ($settings['maintenance_mode'] ?? 'disabled') == 'disabled' ? 'selected' : '' }}>
                                        System Online
                                    </option>
                                    <option value="enabled" {{ ($settings['maintenance_mode'] ?? 'disabled') == 'enabled' ? 'selected' : '' }}>
                                        System Under Maintenance
                                    </option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">When enabled, users will see a maintenance page and cannot log in. Only admins can access the system.</p>
                        </div>

                        <div>
                            <label for="maintenance_message" class="block text-sm font-medium text-gray-700">Maintenance Message</label>
                            <div class="mt-1">
                                <textarea name="maintenance_message" id="maintenance_message" rows="3"
                                          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                          placeholder="We are currently performing scheduled maintenance. Please check back later.">{{ $settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. Please check back later.' }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Custom message to display to users during maintenance.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update preview when form fields change
    const systemNameInput = document.getElementById('system_name');
    const systemDescriptionInput = document.getElementById('system_description');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');

    systemNameInput.addEventListener('input', function() {
        previewName.textContent = this.value;
    });

    systemDescriptionInput.addEventListener('input', function() {
        previewDescription.textContent = this.value;
    });

    // Logo preview
    const logoInput = document.getElementById('system_logo');
    logoInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.bg-gray-50 .flex img');
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    // Create new image element if none exists
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Logo Preview';
                    img.className = 'h-8 w-auto object-contain';
                    document.querySelector('.bg-gray-50 .flex').insertBefore(img, document.querySelector('.bg-gray-50 .flex span'));
                }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Icon preview
    const iconInput = document.getElementById('system_icon');
    iconInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewIcon = document.getElementById('preview-icon');
                const previewPlaceholder = document.getElementById('preview-icon-placeholder');

                if (previewIcon) {
                    previewIcon.src = e.target.result;
                } else if (previewPlaceholder) {
                    // Replace placeholder with image
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
});
</script>
@endsection
