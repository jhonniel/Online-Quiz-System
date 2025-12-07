<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->timestamp('reopen_requested_at')->nullable();
            $table->text('reopen_request_reason')->nullable();
        });

        // Update the enum/check constraint for status column
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE chat_tickets DROP CONSTRAINT IF EXISTS chat_tickets_status_check');
            DB::statement("ALTER TABLE chat_tickets ADD CONSTRAINT chat_tickets_status_check CHECK (status IN ('open', 'closed', 'reopened', 'reopen_requested'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE chat_tickets MODIFY COLUMN status ENUM('open', 'closed', 'reopened', 'reopen_requested') DEFAULT 'open'");
        }
        // SQLite: enum is stored as text, no constraint change needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Revert the enum/check constraint for status column
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE chat_tickets DROP CONSTRAINT IF EXISTS chat_tickets_status_check');
            DB::statement("ALTER TABLE chat_tickets ADD CONSTRAINT chat_tickets_status_check CHECK (status IN ('open', 'closed', 'reopened'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE chat_tickets MODIFY COLUMN status ENUM('open', 'closed', 'reopened') DEFAULT 'open'");
        }

        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->dropColumn(['reopen_requested_at', 'reopen_request_reason']);
        });
    }
};
