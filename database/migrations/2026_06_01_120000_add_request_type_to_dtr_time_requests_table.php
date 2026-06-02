<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dtr_time_requests', function (Blueprint $table) {
            $table->string('request_type', 20)->default('regular')->after('hours');
            $table->uuid('submission_batch')->nullable()->after('request_type');
            $table->decimal('requested_total_hours', 5, 2)->nullable()->after('submission_batch');

            $table->index(['user_id', 'date', 'request_type']);
        });
    }

    public function down(): void
    {
        Schema::table('dtr_time_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date', 'request_type']);
            $table->dropColumn(['request_type', 'submission_batch', 'requested_total_hours']);
        });
    }
};
