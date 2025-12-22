@extends('layouts.admin')

@section('page-title', 'Landing Page Settings')

@push('styles')
<style>
    .form-section {
        @apply bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-6 shadow-sm;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Landing Page Settings</h1>
                    <p class="text-indigo-100 mt-1">Configure your landing page content and appearance</p>
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

    <form action="{{ route('admin.landing-page.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="p-8">
                <!-- Hero Section -->
                <div class="form-section mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-2">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Hero Section</h3>
                            <p class="text-sm text-gray-500">Control the main headline, subtext, buttons, and background image.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="hero_title" class="block text-sm font-medium text-gray-700 mb-2">Headline</label>
                            <input type="text" id="hero_title" name="hero_title"
                                   value="{{ $settings['hero_title'] ?? '' }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="hero_subtitle" class="block text-sm font-medium text-gray-700 mb-2">Subheadline</label>
                            <textarea id="hero_subtitle" name="hero_subtitle" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ $settings['hero_subtitle'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label for="hero_primary_button_text" class="block text-sm font-medium text-gray-700 mb-2">Primary Button Text</label>
                            <input type="text" id="hero_primary_button_text" name="hero_primary_button_text"
                                   value="{{ $settings['hero_primary_button_text'] ?? '' }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="hero_primary_button_url" class="block text-sm font-medium text-gray-700 mb-2">Primary Button URL</label>
                            <input type="url" id="hero_primary_button_url" name="hero_primary_button_url"
                                   value="{{ $settings['hero_primary_button_url'] ?? '' }}"
                                   placeholder="{{ route('login') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="hero_secondary_button_text" class="block text-sm font-medium text-gray-700 mb-2">Secondary Button Text</label>
                            <input type="text" id="hero_secondary_button_text" name="hero_secondary_button_text"
                                   value="{{ $settings['hero_secondary_button_text'] ?? '' }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="hero_secondary_button_url" class="block text-sm font-medium text-gray-700 mb-2">Secondary Button URL</label>
                            <input type="url" id="hero_secondary_button_url" name="hero_secondary_button_url"
                                   value="{{ $settings['hero_secondary_button_url'] ?? '' }}"
                                   placeholder="{{ route('landing.projects') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="hero_background_image" class="block text-sm font-medium text-gray-700 mb-2">Hero Background Image</label>
                            @if(!empty($settings['hero_background_image']))
                                <div class="mb-2">
                                    @php
                                        $heroPreview = null;
                                        try {
                                            $storage = \Illuminate\Support\Facades\Storage::disk('digitalocean');
                                            $imgPath = $settings['hero_background_image'];

                                            if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
                                                $heroPreview = $imgPath;
                                            } elseif ($storage->exists($imgPath)) {
                                                if (method_exists($storage, 'temporaryUrl')) {
                                                    try {
                                                        $heroPreview = $storage->temporaryUrl($imgPath, now()->addHours(24));
                                                    } catch (\Exception $e) {
                                                        $heroPreview = $storage->url($imgPath);
                                                    }
                                                } else {
                                                    $heroPreview = $storage->url($imgPath);
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            $heroPreview = null;
                                        }
                                    @endphp
                                    @if($heroPreview)
                                        <img src="{{ $heroPreview }}" alt="Hero Background" class="w-full max-w-xl rounded-xl object-cover border border-gray-200">
                                    @else
                                        <div class="w-full max-w-xl h-32 rounded-xl bg-gray-200 flex items-center justify-center text-xs text-gray-500">No preview available</div>
                                    @endif
                                </div>
                            @endif
                            <input type="file" id="hero_background_image" name="hero_background_image" accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Optional. Upload a background image for the hero section (recommended: 1920x600px or larger).</p>
                        </div>
                    </div>
                </div>

                <!-- About Us Section -->
                <div class="form-section mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="flex-shrink-0 bg-orange-100 rounded-lg p-2">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">About Us Section</h3>
                            <p class="text-sm text-gray-500">Configure the company information displayed on the landing page</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="about_title" class="block text-sm font-medium text-gray-700 mb-2">Section Title</label>
                            <input type="text" id="about_title" name="about_title"
                                   value="{{ $settings['about_title'] ?? 'About Us' }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="about_subtitle" class="block text-sm font-medium text-gray-700 mb-2">Subtitle (Optional)</label>
                            <input type="text" id="about_subtitle" name="about_subtitle"
                                   value="{{ $settings['about_subtitle'] ?? '' }}"
                                   placeholder="A brief tagline or description"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="about_content" class="block text-sm font-medium text-gray-700 mb-2">About Content</label>
                            <textarea id="about_content" name="about_content" rows="6"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Enter any additional company information, values, or other content...">{{ $settings['about_content'] ?? '' }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">You can use line breaks to format paragraphs. The content will be displayed as formatted text.</p>
                        </div>
                    </div>
                </div>

                <!-- Projects Section -->
                <div class="form-section mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-2">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Projects</h3>
                            <p class="text-sm text-gray-500">Manage projects displayed on the landing page (exactly 3 projects)</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        @for($i = 1; $i <= 3; $i++)
                            <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Project {{ $i }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="project_{{ $i }}_name" class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                                        <input type="text" name="project_{{ $i }}_name" id="project_{{ $i }}_name"
                                               value="{{ $settings['project_' . $i . '_name'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label for="project_{{ $i }}_url" class="block text-sm font-medium text-gray-700 mb-2">Project URL</label>
                                        <input type="url" name="project_{{ $i }}_url" id="project_{{ $i }}_url"
                                               value="{{ $settings['project_' . $i . '_url'] ?? '' }}"
                                               placeholder="https://example.com"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="project_{{ $i }}_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea name="project_{{ $i }}_description" id="project_{{ $i }}_description" rows="2"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ $settings['project_' . $i . '_description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="project_{{ $i }}_image" class="block text-sm font-medium text-gray-700 mb-2">Project Image</label>
                                        @if(!empty($settings['project_' . $i . '_image']))
                                            <div class="mb-2">
                                                @php
                                                    $previewUrl = null;
                                                    try {
                                                        $storage = \Illuminate\Support\Facades\Storage::disk('digitalocean');
                                                        $imgPath = $settings['project_' . $i . '_image'];

                                                        if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
                                                            $previewUrl = $imgPath;
                                                        } elseif ($storage->exists($imgPath)) {
                                                            if (method_exists($storage, 'temporaryUrl')) {
                                                                try {
                                                                    $previewUrl = $storage->temporaryUrl($imgPath, now()->addHours(24));
                                                                } catch (\Exception $e) {
                                                                    $previewUrl = $storage->url($imgPath);
                                                                }
                                                            } else {
                                                                $previewUrl = $storage->url($imgPath);
                                                            }
                                                        }
                                                    } catch (\Exception $e) {
                                                        $previewUrl = null;
                                                    }
                                                @endphp
                                                @if($previewUrl)
                                                    <img src="{{ $previewUrl }}" alt="Project {{ $i }}" class="w-32 h-32 rounded-lg object-cover">
                                                @else
                                                    <div class="w-32 h-32 rounded-lg bg-gray-200 flex items-center justify-center text-xs text-gray-500">No preview</div>
                                                @endif
                                            </div>
                                        @endif
                                        <input type="file" name="project_{{ $i }}_image" id="project_{{ $i }}_image" accept="image/*"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <p class="mt-1 text-xs text-gray-500">Upload a square image (recommended: 800x800px or larger)</p>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Additional Projects Section -->
                <div class="form-section mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-2">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Additional Projects</h3>
                                <p class="text-sm text-gray-500">Add more projects to display on the landing page</p>
                            </div>
                        </div>
                        <button type="button" onclick="addAdditionalProject()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add Project</span>
                        </button>
                    </div>

                    <div id="additional-projects-container" class="space-y-6">
                        @php
                            $additionalProjects = json_decode(\App\Models\Setting::get('additional_projects', '[]'), true) ?? [];
                        @endphp
                        @if(!empty($additionalProjects))
                            @foreach($additionalProjects as $index => $project)
                                <div class="border border-gray-200 rounded-lg p-6 bg-gray-50 additional-project-item" data-index="{{ $index }}">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-md font-semibold text-gray-900">Additional Project {{ $index + 1 }}</h4>
                                        <button type="button" onclick="removeAdditionalProject(this)" class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm flex items-center space-x-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                                            <input type="text" name="additional_projects[{{ $index }}][name]" value="{{ $project['name'] ?? '' }}"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Project URL</label>
                                            <input type="url" name="additional_projects[{{ $index }}][url]" value="{{ $project['url'] ?? '' }}"
                                                   placeholder="https://example.com"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                            <textarea name="additional_projects[{{ $index }}][description]" rows="2"
                                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ $project['description'] ?? '' }}</textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Image</label>
                                            @if(!empty($project['image']))
                                                <div class="mb-2">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('digitalocean')->exists($project['image']) ? \Illuminate\Support\Facades\Storage::disk('digitalocean')->url($project['image']) : $project['image'] }}"
                                                         alt="Additional Project {{ $index + 1 }}" class="w-32 h-32 rounded-lg object-cover">
                                                </div>
                                            @endif
                                            <input type="file" name="additional_projects[{{ $index }}][image]" accept="image/*"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <input type="hidden" name="additional_projects[{{ $index }}][existing_image]" value="{{ $project['image'] ?? '' }}">
                                            <p class="mt-1 text-xs text-gray-500">Upload a square image (recommended: 800x800px or larger)</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <input type="hidden" id="additional-projects-count" name="additional_projects_count" value="{{ count($additionalProjects) }}">
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
        </div>
    </form>
</div>

<script>
// Additional Projects Management
let additionalProjectsCount = parseInt(document.getElementById('additional-projects-count')?.value || '0') || 0;

function addAdditionalProject() {
    const container = document.getElementById('additional-projects-container');
    if (!container) return;

    const index = additionalProjectsCount;
    additionalProjectsCount++;

    const projectHtml = `
        <div class="border border-gray-200 rounded-lg p-6 bg-gray-50 additional-project-item" data-index="${index}">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-gray-900">Additional Project ${index + 1}</h4>
                <button type="button" onclick="removeAdditionalProject(this)" class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Remove</span>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                    <input type="text" name="additional_projects[${index}][name]" value=""
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project URL</label>
                    <input type="url" name="additional_projects[${index}][url]" value=""
                           placeholder="https://example.com"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="additional_projects[${index}][description]" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Image</label>
                    <input type="file" name="additional_projects[${index}][image]" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="hidden" name="additional_projects[${index}][existing_image]" value="">
                    <p class="mt-1 text-xs text-gray-500">Upload a square image (recommended: 800x800px or larger)</p>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', projectHtml);
    document.getElementById('additional-projects-count').value = additionalProjectsCount;
}

function removeAdditionalProject(button) {
    const item = button.closest('.additional-project-item');
    if (item) {
        item.remove();
        // Update indices and count
        const items = document.querySelectorAll('.additional-project-item');
        items.forEach((item, index) => {
            item.querySelector('h4').textContent = `Additional Project ${index + 1}`;
            const inputs = item.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/additional_projects\[\d+\]/, `additional_projects[${index}]`);
                }
            });
        });
        document.getElementById('additional-projects-count').value = items.length;
    }
}
</script>
@endsection
