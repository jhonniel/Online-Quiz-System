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
        if (! Schema::getConnection()->isDoctrineAvailable()) {
            return;
        }

        Schema::table('hiring_applications', function (Blueprint $table) {
            $table->datetime('interview_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::getConnection()->isDoctrineAvailable()) {
            return;
        }

        Schema::table('hiring_applications', function (Blueprint $table) {
            $table->date('interview_date')->nullable()->change();
        });
    }
};
