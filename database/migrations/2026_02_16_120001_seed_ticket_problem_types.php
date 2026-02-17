<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['slug' => 'technical', 'label' => 'Technical Issue', 'sort_order' => 1],
            ['slug' => 'access', 'label' => 'Access / Login Problem', 'sort_order' => 2],
            ['slug' => 'content', 'label' => 'Content Error', 'sort_order' => 3],
            ['slug' => 'performance', 'label' => 'Performance / Slow', 'sort_order' => 4],
            ['slug' => 'bug', 'label' => 'Bug / Malfunction', 'sort_order' => 5],
            ['slug' => 'other', 'label' => 'Other', 'sort_order' => 99],
        ];
        foreach ($defaults as $row) {
            DB::table('ticket_problem_types')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        // optional: delete seeded rows by slug
    }
};
