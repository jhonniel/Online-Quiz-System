<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'totp_secret')) {
                $table->text('totp_secret')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'totp_confirmed_at')) {
                $table->timestamp('totp_confirmed_at')->nullable()->after('totp_secret');
            }
            if (! Schema::hasColumn('users', 'totp_recovery_codes')) {
                $table->text('totp_recovery_codes')->nullable()->after('totp_confirmed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['totp_recovery_codes', 'totp_confirmed_at', 'totp_secret'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
