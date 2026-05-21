<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('questions') || ! Schema::hasColumn('questions', 'correct_answer')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE questions ALTER COLUMN correct_answer TYPE TEXT');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE questions MODIFY correct_answer TEXT NULL');
        } else {
            // sqlite and others: column type is usually flexible enough for reference text
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('questions') || ! Schema::hasColumn('questions', 'correct_answer')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE questions ALTER COLUMN correct_answer TYPE VARCHAR(1)');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE questions MODIFY correct_answer VARCHAR(1) NULL');
        }
    }
};
