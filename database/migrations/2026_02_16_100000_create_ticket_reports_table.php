<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 32)->unique();
            $table->string('type'); // problem type
            $table->text('description');
            $table->string('full_name');
            $table->string('contact_number')->nullable();
            $table->string('email');
            $table->string('office')->nullable();
            $table->text('address')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 20)->default('open'); // open, closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_reports');
    }
};
