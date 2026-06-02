<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('dtr_time_request_id')
                ->nullable()
                ->after('user_id')
                ->constrained('dtr_time_requests')
                ->nullOnDelete();

            $table->unique('dtr_time_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['dtr_time_request_id']);
            $table->dropUnique(['dtr_time_request_id']);
            $table->dropColumn('dtr_time_request_id');
        });
    }
};
