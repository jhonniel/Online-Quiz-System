<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->boolean('tasks')->default(false)->after('confession');
            $table->json('allowed_task_features')->nullable()->after('tasks');
        });
    }

    public function down(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->dropColumn(['tasks', 'allowed_task_features']);
        });
    }
};
