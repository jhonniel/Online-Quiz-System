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
        // Check if task_lists table exists before adding foreign key
        if (Schema::hasTable('task_lists')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'task_list_id')) {
                    $table->foreignId('task_list_id')->nullable()->after('parent_id')->constrained('task_lists')->onDelete('cascade');
                    $table->index('task_list_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_list_id']);
            $table->dropColumn('task_list_id');
        });
    }
};
