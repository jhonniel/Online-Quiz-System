<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema-only marker migration.
     *
     * Run reconciliation separately (avoid SQLite locks during migrate):
     * php artisan students:process-ojt-post-completion
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'ojt_requirement_met_at')) {
            return;
        }
    }

    public function down(): void
    {
        //
    }
};
