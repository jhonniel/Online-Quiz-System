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
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'moa_document_path')) {
                $table->string('moa_document_path')->nullable()->after('cover_photo');
            }
            if (! Schema::hasColumn('users', 'moa_uploaded_at')) {
                $table->timestamp('moa_uploaded_at')->nullable()->after('moa_document_path');
            }
            if (! Schema::hasColumn('users', 'moa_reupload_allowed')) {
                $table->boolean('moa_reupload_allowed')->default(false)->after('moa_uploaded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'moa_reupload_allowed')) {
                $table->dropColumn('moa_reupload_allowed');
            }
            if (Schema::hasColumn('users', 'moa_uploaded_at')) {
                $table->dropColumn('moa_uploaded_at');
            }
            if (Schema::hasColumn('users', 'moa_document_path')) {
                $table->dropColumn('moa_document_path');
            }
        });
    }
};
