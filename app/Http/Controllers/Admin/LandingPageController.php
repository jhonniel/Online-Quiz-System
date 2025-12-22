<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LandingPageController extends Controller
{
    public function index()
    {
        $settings = [];

        // Load landing page settings
        // Hero section
        $settings['hero_title'] = Setting::get('hero_title', 'Transform Your Assessment Experience');
        $settings['hero_subtitle'] = Setting::get('hero_subtitle', 'A powerful, intuitive platform designed for modern educational assessment and learning management.');
        $settings['hero_primary_button_text'] = Setting::get('hero_primary_button_text', 'Get Started Free');
        $settings['hero_primary_button_url'] = Setting::get('hero_primary_button_url', route('login'));
        $settings['hero_secondary_button_text'] = Setting::get('hero_secondary_button_text', 'Explore Features');
        $settings['hero_secondary_button_url'] = Setting::get('hero_secondary_button_url', route('landing.projects'));
        $settings['hero_background_image'] = Setting::get('hero_background_image', null);

        // Team members
        for ($i = 1; $i <= 4; $i++) {
            $settings["employee_{$i}_name"] = Setting::get("employee_{$i}_name", '');
            $settings["employee_{$i}_position"] = Setting::get("employee_{$i}_position", '');
            $settings["employee_{$i}_image"] = Setting::get("employee_{$i}_image", null);
        }

        for ($i = 1; $i <= 3; $i++) {
            $settings["project_{$i}_name"] = Setting::get("project_{$i}_name", '');
            $settings["project_{$i}_description"] = Setting::get("project_{$i}_description", '');
            $settings["project_{$i}_image"] = Setting::get("project_{$i}_image", null);
            $settings["project_{$i}_url"] = Setting::get("project_{$i}_url", '');
        }

        // About Us section
        $settings['about_title'] = Setting::get('about_title', 'About Us');
        $settings['about_subtitle'] = Setting::get('about_subtitle', '');
        $settings['about_content'] = Setting::get('about_content', '');

        return view('admin.landing-page.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Hero
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_primary_button_text' => 'nullable|string|max:100',
            'hero_primary_button_url' => 'nullable|url|max:500',
            'hero_secondary_button_text' => 'nullable|string|max:100',
            'hero_secondary_button_url' => 'nullable|url|max:500',
            'hero_background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',

            // About Us
            'about_title' => 'nullable|string|max:255',
            'about_subtitle' => 'nullable|string|max:500',
            'about_content' => 'nullable|string|max:5000',

            // Employees
            'employee_1_name' => 'nullable|string|max:255',
            'employee_1_position' => 'nullable|string|max:255',
            'employee_1_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'employee_2_name' => 'nullable|string|max:255',
            'employee_2_position' => 'nullable|string|max:255',
            'employee_2_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'employee_3_name' => 'nullable|string|max:255',
            'employee_3_position' => 'nullable|string|max:255',
            'employee_3_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'employee_4_name' => 'nullable|string|max:255',
            'employee_4_position' => 'nullable|string|max:255',
            'employee_4_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // Projects
            'project_1_name' => 'nullable|string|max:255',
            'project_1_description' => 'nullable|string|max:500',
            'project_1_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'project_1_url' => 'nullable|url|max:500',
            'project_2_name' => 'nullable|string|max:255',
            'project_2_description' => 'nullable|string|max:500',
            'project_2_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'project_2_url' => 'nullable|url|max:500',
            'project_3_name' => 'nullable|string|max:255',
            'project_3_description' => 'nullable|string|max:500',
            'project_3_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'project_3_url' => 'nullable|url|max:500',
            // Additional Projects
            'additional_projects' => 'nullable|array',
            'additional_projects.*.name' => 'nullable|string|max:255',
            'additional_projects.*.description' => 'nullable|string|max:500',
            'additional_projects.*.url' => 'nullable|url|max:500',
            'additional_projects.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'additional_projects.*.existing_image' => 'nullable|string|max:500',
        ]);

        $assetDisk = 'digitalocean';
        $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $heroDir = $assetRoot ? $assetRoot . '/landing/hero' : 'landing/hero';
        $employeeDir = $assetRoot ? $assetRoot . '/landing/employees' : 'landing/employees';
        $projectDir = $assetRoot ? $assetRoot . '/landing/projects' : 'landing/projects';

        // Handle Landing Page - Hero (text and buttons)
        Setting::set('hero_title', $request->input('hero_title', ''), 'text', 'Landing page hero title');
        Setting::set('hero_subtitle', $request->input('hero_subtitle', ''), 'text', 'Landing page hero subtitle');
        Setting::set('hero_primary_button_text', $request->input('hero_primary_button_text', ''), 'text', 'Hero primary button text');
        Setting::set('hero_primary_button_url', $request->input('hero_primary_button_url', ''), 'text', 'Hero primary button URL');
        Setting::set('hero_secondary_button_text', $request->input('hero_secondary_button_text', ''), 'text', 'Hero secondary button text');
        Setting::set('hero_secondary_button_url', $request->input('hero_secondary_button_url', ''), 'text', 'Hero secondary button URL');

        // Handle Landing Page - About Us
        Setting::set('about_title', $request->input('about_title', 'About Us'), 'text', 'About Us section title');
        Setting::set('about_subtitle', $request->input('about_subtitle', ''), 'text', 'About Us section subtitle');
        Setting::set('about_content', $request->input('about_content', ''), 'text', 'About Us section content');

        // Handle Landing Page - Hero background image
        if ($request->hasFile('hero_background_image')) {
            $oldHero = Setting::get('hero_background_image');
            if ($oldHero && Storage::disk($assetDisk)->exists($oldHero)) {
                Storage::disk($assetDisk)->delete($oldHero);
            }

            $heroPath = $request->file('hero_background_image')->store($heroDir, $assetDisk);

            if ($heroPath && Storage::disk($assetDisk)->exists($heroPath)) {
                Setting::set('hero_background_image', $heroPath, 'image', 'Landing page hero background image');
            } else {
                Log::error('Failed to save hero background image to Spaces', [
                    'path' => $heroPath,
                    'directory' => $heroDir,
                ]);
            }
        }

        // Handle Landing Page - Employees
        for ($i = 1; $i <= 4; $i++) {
            // Save employee name and position
            $employeeName = $request->input("employee_{$i}_name") ?? '';
            $employeePosition = $request->input("employee_{$i}_position") ?? '';
            Setting::set("employee_{$i}_name", $employeeName, 'text', "Employee {$i} name");
            Setting::set("employee_{$i}_position", $employeePosition, 'text', "Employee {$i} position");

            // Handle employee image upload
            if ($request->hasFile("employee_{$i}_image")) {
                // Delete old image if exists
                $oldImage = Setting::get("employee_{$i}_image");
                if ($oldImage && Storage::disk($assetDisk)->exists($oldImage)) {
                    Storage::disk($assetDisk)->delete($oldImage);
                }

                // Store new image to DigitalOcean Spaces
                $imagePath = $request->file("employee_{$i}_image")->store($employeeDir, $assetDisk);

                // Verify the image was saved
                if ($imagePath && Storage::disk($assetDisk)->exists($imagePath)) {
                    Setting::set("employee_{$i}_image", $imagePath, 'image', "Employee {$i} photo");
                } else {
                    \Log::error('Failed to save employee image to Spaces', [
                        'employee_index' => $i,
                        'path' => $imagePath,
                        'directory' => $employeeDir
                    ]);
                }
            }
        }

        // Handle Landing Page - Projects
        for ($i = 1; $i <= 3; $i++) {
            // Save project name, description, and URL
            $projectName = $request->input("project_{$i}_name") ?? '';
            $projectDescription = $request->input("project_{$i}_description") ?? '';
            $projectUrl = $request->input("project_{$i}_url") ?? '';
            Setting::set("project_{$i}_name", $projectName, 'text', "Project {$i} name");
            Setting::set("project_{$i}_description", $projectDescription, 'text', "Project {$i} description");
            Setting::set("project_{$i}_url", $projectUrl, 'text', "Project {$i} URL");

            // Handle project image upload
            if ($request->hasFile("project_{$i}_image")) {
                // Delete old image if exists
                $oldImage = Setting::get("project_{$i}_image");
                if ($oldImage && Storage::disk($assetDisk)->exists($oldImage)) {
                    Storage::disk($assetDisk)->delete($oldImage);
                }

                // Store new image to DigitalOcean Spaces
                $imagePath = $request->file("project_{$i}_image")->store($projectDir, $assetDisk);

                // Verify the image was saved
                if ($imagePath && Storage::disk($assetDisk)->exists($imagePath)) {
                    Setting::set("project_{$i}_image", $imagePath, 'image', "Project {$i} image");
                } else {
                    \Log::error('Failed to save project image to Spaces', [
                        'project_index' => $i,
                        'path' => $imagePath,
                        'directory' => $projectDir
                    ]);
                }
            }
        }

        // Handle Landing Page - Additional Projects
        $additionalProjects = $request->input('additional_projects', []);
        $additionalProjectsData = [];

        if (!empty($additionalProjects) && is_array($additionalProjects)) {
            foreach ($additionalProjects as $index => $projectData) {
                if (empty($projectData['name'])) {
                    continue; // Skip empty projects
                }

                $project = [
                    'name' => $projectData['name'] ?? '',
                    'description' => $projectData['description'] ?? '',
                    'url' => $projectData['url'] ?? '#',
                    'image' => $projectData['existing_image'] ?? null,
                ];

                // Handle image upload for additional projects
                if ($request->hasFile("additional_projects.{$index}.image")) {
                    // Delete old image if exists
                    $oldImage = $projectData['existing_image'] ?? null;
                    if ($oldImage && Storage::disk($assetDisk)->exists($oldImage)) {
                        Storage::disk($assetDisk)->delete($oldImage);
                    }

                    // Store new image to DigitalOcean Spaces
                    $imagePath = $request->file("additional_projects.{$index}.image")->store($projectDir, $assetDisk);

                    // Verify the image was saved
                    if ($imagePath && Storage::disk($assetDisk)->exists($imagePath)) {
                        $project['image'] = $imagePath;
                    } else {
                        \Log::error('Failed to save additional project image to Spaces', [
                            'project_index' => $index,
                            'path' => $imagePath,
                            'directory' => $projectDir
                        ]);
                        // Keep existing image if upload failed
                        $project['image'] = $projectData['existing_image'] ?? null;
                    }
                }

                $additionalProjectsData[] = $project;
            }
        }

        // Save additional projects as JSON
        Setting::set('additional_projects', json_encode($additionalProjectsData), 'text', 'Additional projects for landing page');

        // Clear cache
        for ($i = 1; $i <= 4; $i++) {
            Cache::forget("setting.employee_{$i}_name");
            Cache::forget("setting.employee_{$i}_position");
            Cache::forget("setting.employee_{$i}_image");
        }
        for ($i = 1; $i <= 3; $i++) {
            Cache::forget("setting.project_{$i}_name");
            Cache::forget("setting.project_{$i}_description");
            Cache::forget("setting.project_{$i}_image");
            Cache::forget("setting.project_{$i}_url");
        }
        Cache::forget('setting.additional_projects');

        // Clear hero settings cache
        Cache::forget('setting.hero_title');
        Cache::forget('setting.hero_subtitle');
        Cache::forget('setting.hero_primary_button_text');
        Cache::forget('setting.hero_primary_button_url');
        Cache::forget('setting.hero_secondary_button_text');
        Cache::forget('setting.hero_secondary_button_url');
        Cache::forget('setting.hero_background_image');

        // Clear about settings cache
        Cache::forget('setting.about_title');
        Cache::forget('setting.about_subtitle');
        Cache::forget('setting.about_content');

        Setting::clearCache();

        return redirect()->route('admin.landing-page.index')
            ->with('success', 'Landing page settings updated successfully.');
    }
}
