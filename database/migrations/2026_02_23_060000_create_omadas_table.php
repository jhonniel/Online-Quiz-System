<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omadas', function (Blueprint $table) {
            $table->id();
            $table->string('account_linked_email')->nullable()->index();
            $table->string('site')->nullable();
            $table->string('office')->nullable();
            $table->string('type')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('license')->nullable();
            $table->date('license_expiration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omadas');
    }
};
