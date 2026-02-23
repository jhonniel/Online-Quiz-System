<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_statements', function (Blueprint $table) {
            $table->id();
            $table->json('starlink_ids'); // IDs of Starlinks marked as paid
            $table->string('type', 20); // ongoing or overdue
            $table->foreignId('marked_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_statements');
    }
};
