<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->foreignId('assigned_to_user_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to_user_id');
        });
    }
};

