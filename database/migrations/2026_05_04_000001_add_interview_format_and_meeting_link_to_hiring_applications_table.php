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
            $table->string('interview_format', 20)->default('on_site')->after('interview_date');
            $table->text('interview_meeting_link')->nullable()->after('interview_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hiring_applications', function (Blueprint $table) {
            $table->dropColumn(['interview_format', 'interview_meeting_link']);
        });
    }
};
