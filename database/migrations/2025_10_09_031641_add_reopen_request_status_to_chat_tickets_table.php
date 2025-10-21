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
        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->enum('status', ['open', 'closed', 'reopened', 'reopen_requested'])->default('open')->change();
            $table->timestamp('reopen_requested_at')->nullable()->after('reopened_at');
            $table->text('reopen_request_reason')->nullable()->after('reopen_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->enum('status', ['open', 'closed', 'reopened'])->default('open')->change();
            $table->dropColumn(['reopen_requested_at', 'reopen_request_reason']);
        });
    }
};
