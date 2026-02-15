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
        Schema::create('confession_post_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('confession_post_id')->constrained('confession_posts')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->tinyInteger('vote'); // 1 or -1
            $table->timestamps();
            $table->unique(['confession_post_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confession_post_votes');
    }
};
