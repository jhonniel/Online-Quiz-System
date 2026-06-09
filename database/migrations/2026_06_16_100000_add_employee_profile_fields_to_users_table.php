<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_hired')->nullable()->after('department_id');
            $table->string('tin', 50)->nullable()->after('date_hired');
            $table->string('sss_number', 50)->nullable()->after('tin');
            $table->string('hdmf_number', 50)->nullable()->after('sss_number');
            $table->string('phic_number', 50)->nullable()->after('hdmf_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_hired', 'tin', 'sss_number', 'hdmf_number', 'phic_number']);
        });
    }
};
