<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\View;

return new class extends Migration
{
    /**
     * When student_rules_regulations_html is missing or empty, store the built-in
     * default rules body in the database so the agreement modal content is always DB-backed.
     */
    public function up(): void
    {
        $current = Setting::get('student_rules_regulations_html', '');
        if (is_string($current) && trim($current) !== '') {
            return;
        }

        $html = View::make('components.student-rules-regulations-default-body')->render();
        Setting::set(
            'student_rules_regulations_html',
            $html,
            'text',
            'HTML body for student rules and regulations login modal (includes default when seeded)'
        );
    }

    public function down(): void
    {
        // Do not remove: admins may have edited since seed; leave row as-is.
    }
};
