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
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('users', function (Blueprint $table) use ($driver) {
            if ($driver === 'sqlite') {
                // Use string for SQLite to avoid enum CHECK constraint issues
                $table->string('role', 50)->default('user')->after('email');
            } else {
                $table->enum('role', ['admin', 'user'])->default('user')->after('email');
            }

            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
