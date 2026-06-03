<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'student_rules_merit_automation_disabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('student_rules_merit_automation_disabled')->default(false)->after('student_rules_marquee_manual');
            });
        }

        Setting::set(
            'student_merit_auto_notices_enabled',
            'enabled',
            'text',
            'Enable automatic student rules notices from merit counts (enabled or disabled)'
        );
        Setting::set(
            'student_merit_violation_warning_threshold',
            '1',
            'number',
            'Minimum total merits to auto-enable rules violation warning'
        );
        Setting::set(
            'student_merit_final_notice_threshold',
            '3',
            'number',
            'Minimum total merits to auto-enable final notice (scrolling banner)'
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'student_rules_merit_automation_disabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('student_rules_merit_automation_disabled');
            });
        }
    }
};
