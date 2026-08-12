<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('leave_requests', 'admin_officially_excused')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->boolean('admin_officially_excused')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('leave_requests', 'admin_officially_excused')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('admin_officially_excused');
        });
    }
};
