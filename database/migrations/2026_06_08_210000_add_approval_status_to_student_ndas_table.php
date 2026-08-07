<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_ndas')) {
            return;
        }

        if (! Schema::hasColumn('student_ndas', 'approval_status')) {
            Schema::table('student_ndas', function (Blueprint $table) {
                $table->string('approval_status', 20)->nullable();
            });
        }

        if (! Schema::hasColumn('student_ndas', 'reviewed_by')) {
            Schema::table('student_ndas', function (Blueprint $table) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('student_ndas', 'reviewed_at')) {
            Schema::table('student_ndas', function (Blueprint $table) {
                $table->timestamp('reviewed_at')->nullable();
            });
        }

        if (! Schema::hasColumn('student_ndas', 'review_notes')) {
            Schema::table('student_ndas', function (Blueprint $table) {
                $table->text('review_notes')->nullable();
            });
        }

        DB::table('student_ndas')
            ->whereNotNull('signed_document_path')
            ->where('signed_document_path', '!=', '')
            ->whereNull('approval_status')
            ->update([
                'approval_status' => 'approved',
                'reviewed_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_ndas')) {
            return;
        }

        if (Schema::hasColumn('student_ndas', 'reviewed_by')) {
            Schema::table('student_ndas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('reviewed_by');
            });
        }

        $columns = collect(['approval_status', 'reviewed_at', 'review_notes'])
            ->filter(fn (string $column) => Schema::hasColumn('student_ndas', $column))
            ->values()
            ->all();

        if ($columns !== []) {
            Schema::table('student_ndas', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
