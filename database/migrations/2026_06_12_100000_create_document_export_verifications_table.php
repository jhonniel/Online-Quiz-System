<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_export_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('reference_code', 32)->unique();
            $table->string('document_type', 100);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_export_verifications');
    }
};
