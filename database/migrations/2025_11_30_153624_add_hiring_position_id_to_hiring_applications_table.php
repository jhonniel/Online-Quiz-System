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
            // Remove unique constraint on email (same person can apply to multiple positions)
            $table->dropUnique(['email']);
            
            // Add hiring position relationship
            $table->foreignId('hiring_position_id')->nullable()->after('id')->constrained('hiring_positions')->nullOnDelete();
            
            // Add unique constraint for email + position combination
            $table->unique(['email', 'hiring_position_id'], 'unique_email_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hiring_applications', function (Blueprint $table) {
            $table->dropForeign(['hiring_position_id']);
            $table->dropUnique('unique_email_position');
            $table->dropColumn('hiring_position_id');
            $table->unique('email');
        });
    }
};
