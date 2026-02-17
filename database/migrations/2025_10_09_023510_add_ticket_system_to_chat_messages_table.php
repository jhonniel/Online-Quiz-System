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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('ticket_number')->nullable()->after('id');
            $table->enum('status', ['open', 'closed'])->default('open')->after('sender_type');
            $table->timestamp('closed_at')->nullable()->after('read_at');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null')->after('closed_at');
            $table->text('close_reason')->nullable()->after('closed_by');
        });

        // Create index for ticket_number for faster lookups
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index('ticket_number');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['ticket_number']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['ticket_number', 'status', 'closed_at', 'closed_by', 'close_reason']);
        });
    }
};