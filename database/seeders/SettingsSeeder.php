<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::set('system_name', 'Quiz System', 'text', 'The name of the quiz system');
        Setting::set('system_logo', null, 'image', 'The system logo');
        Setting::set('system_description', 'Online Quiz Management System', 'text', 'System description');
        Setting::set('primary_color', '#4F46E5', 'color', 'Primary color for the system');
        Setting::set('secondary_color', '#6B7280', 'color', 'Secondary color for the system');
    }
}
