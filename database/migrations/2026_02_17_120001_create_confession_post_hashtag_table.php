<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confession_post_hashtag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('confession_post_id')->constrained('confession_posts')->cascadeOnDelete();
            $table->foreignId('confession_hashtag_id')->constrained('confession_hashtags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['confession_post_id', 'confession_hashtag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confession_post_hashtag');
    }
};
