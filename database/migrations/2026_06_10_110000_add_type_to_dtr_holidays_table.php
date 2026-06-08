<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dtr_holidays', function (Blueprint $table) {
            $table->string('type', 20)->default('regular')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('dtr_holidays', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
