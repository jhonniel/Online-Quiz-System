<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'student_manual_merits')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('student_manual_merits')->default(0)->after('student_absence_allowance');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'student_manual_merits')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('student_manual_merits');
        });
    }
};
