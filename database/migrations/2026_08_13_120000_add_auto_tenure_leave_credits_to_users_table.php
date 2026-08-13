<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('auto_tenure_leave_credits_enabled')
                ->default(false)
                ->after('date_hired');
            $table->unsignedTinyInteger('auto_tenure_leave_credits_last_tier')
                ->nullable()
                ->after('auto_tenure_leave_credits_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'auto_tenure_leave_credits_enabled',
                'auto_tenure_leave_credits_last_tier',
            ]);
        });
    }
};
