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
        Schema::create('custom_priorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('color', 50)->default('gray'); // Color class or hex code
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'order']);
            $table->unique(['user_id', 'name']); // Prevent duplicate names per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_priorities');
    }
};
