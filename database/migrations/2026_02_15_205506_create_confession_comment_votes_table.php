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
        Schema::create('confession_comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('confession_comment_id')->constrained('confession_comments')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->tinyInteger('vote'); // 1 or -1
            $table->timestamps();
            $table->unique(['confession_comment_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confession_comment_votes');
    }
};
