<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            $table->unsignedBigInteger('replacement_group_id')->nullable()->index()->after('linked_account_id');
            $table->date('replaced_at')->nullable()->after('status');
            $table->string('replacement_note')->nullable()->after('replaced_at');
        });
    }

    public function down(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            $table->dropColumn(['replacement_group_id', 'replaced_at', 'replacement_note']);
        });
    }
};
