<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'student_rules_warning')) {
                $table->boolean('student_rules_warning')->default(false);
            }
            if (!Schema::hasColumn('users', 'student_rules_marquee_enabled')) {
                $table->boolean('student_rules_marquee_enabled')->default(false);
            }
            if (!Schema::hasColumn('users', 'student_rules_marquee_text')) {
                $table->text('student_rules_marquee_text')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'student_rules_marquee_text')) {
                $table->dropColumn('student_rules_marquee_text');
            }
            if (Schema::hasColumn('users', 'student_rules_marquee_enabled')) {
                $table->dropColumn('student_rules_marquee_enabled');
            }
            if (Schema::hasColumn('users', 'student_rules_warning')) {
                $table->dropColumn('student_rules_warning');
            }
        });
    }
};
