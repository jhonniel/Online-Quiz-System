<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starlinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linked_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('account_linked_email')->nullable()->index();
            $table->string('starlink_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('kit_number')->nullable();
            $table->string('router_id')->nullable();
            $table->string('ssid')->nullable();
            $table->string('wifi_password')->nullable();
            $table->string('office_location')->nullable();
            $table->date('start_date')->nullable();
            $table->string('po_no')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('plan')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('end_user_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starlinks');
    }
};
