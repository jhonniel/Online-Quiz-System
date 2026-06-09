<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'p12_certificate_path')) {
                $table->string('p12_certificate_path')->nullable()->after('e_signature_path');
            }
            if (! Schema::hasColumn('users', 'p12_certificate_password')) {
                $table->text('p12_certificate_password')->nullable()->after('p12_certificate_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'p12_certificate_password')) {
                $table->dropColumn('p12_certificate_password');
            }
            if (Schema::hasColumn('users', 'p12_certificate_path')) {
                $table->dropColumn('p12_certificate_path');
            }
        });
    }
};
