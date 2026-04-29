<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->string('admin_attachment_path')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->dropColumn('admin_attachment_path');
        });
    }
};
