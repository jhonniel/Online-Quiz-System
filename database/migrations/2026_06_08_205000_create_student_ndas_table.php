<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_ndas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('id_number');
            $table->string('valid_id_type');
            $table->string('city')->default('Davao');
            $table->date('agreement_date');
            $table->string('signed_document_path')->nullable();
            $table->string('storage_disk')->nullable();
            $table->timestamp('signed_uploaded_at')->nullable();
            $table->boolean('reupload_allowed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_ndas');
    }
};
