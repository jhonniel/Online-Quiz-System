<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Teachers are not assigned to departments; clear any legacy values.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'teacher')->update(['department_id' => null]);
    }

    /**
     * Cannot restore previous department assignments.
     */
    public function down(): void
    {
        //
    }
};
