<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_performance_ratings')) {
            return;
        }

        Schema::table('student_performance_ratings', function (Blueprint $table) {
            if (! Schema::hasColumn('student_performance_ratings', 'rated_by_name')) {
                $table->string('rated_by_name')->nullable();
            }
            if (! Schema::hasColumn('student_performance_ratings', 'rated_by_email')) {
                $table->string('rated_by_email')->nullable();
            }
            if (! Schema::hasColumn('student_performance_ratings', 'rated_at')) {
                $table->timestamp('rated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_performance_ratings')) {
            return;
        }

        Schema::table('student_performance_ratings', function (Blueprint $table) {
            $columns = [];
            foreach (['rated_by_name', 'rated_by_email', 'rated_at'] as $column) {
                if (Schema::hasColumn('student_performance_ratings', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
