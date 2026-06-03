<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'student_rules_warning_manual')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('student_rules_warning_manual')->default(false)->after('student_rules_warning');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'student_rules_warning_manual')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('student_rules_warning_manual');
            });
        }
    }
};
