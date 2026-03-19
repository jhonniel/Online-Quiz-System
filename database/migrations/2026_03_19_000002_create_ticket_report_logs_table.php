<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_report_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_report_id')->constrained('ticket_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 50); // processed, needs_investigation, resolved, reopened, notes_updated, etc.
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->string('meta', 255)->nullable(); // short extra info (optional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_report_logs');
    }
};

