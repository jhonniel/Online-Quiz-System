<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hiring_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('hiring_applications', 'school')) {
                $table->string('school')->nullable()->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hiring_applications', function (Blueprint $table) {
            if (Schema::hasColumn('hiring_applications', 'school')) {
                $table->dropColumn('school');
            }
        });
    }
};

