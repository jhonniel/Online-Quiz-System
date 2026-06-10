<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_announcement_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('system_announcement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->unique(['user_id', 'system_announcement_id'], 'employee_announcement_ack_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_announcement_acknowledgments');
        Schema::dropIfExists('system_announcements');
    }
};
