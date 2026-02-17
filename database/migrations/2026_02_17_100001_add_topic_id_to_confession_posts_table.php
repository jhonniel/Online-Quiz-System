<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confession_posts', function (Blueprint $table) {
            $table->foreignId('confession_topic_id')->nullable()->constrained('confession_topics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('confession_posts', function (Blueprint $table) {
            $table->dropForeign(['confession_topic_id']);
        });
    }
};
