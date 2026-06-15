<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_ndas', function (Blueprint $table) {
            $table->string('approval_status', 20)->nullable()->after('reupload_allowed');
            $table->foreignId('reviewed_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
        });

        DB::table('student_ndas')
            ->whereNotNull('signed_document_path')
            ->where('signed_document_path', '!=', '')
            ->update([
                'approval_status' => 'approved',
                'reviewed_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('student_ndas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['approval_status', 'reviewed_at', 'review_notes']);
        });
    }
};
