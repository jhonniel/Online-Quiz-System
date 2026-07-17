<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sayit_chat_rooms', function (Blueprint $table) {
            $table->string('freeze_code', 32)->nullable()->after('avatar_path');
            $table->string('delete_code', 32)->nullable()->after('freeze_code');
            $table->string('gibberish_code', 32)->nullable()->after('delete_code');

            $table->boolean('is_frozen')->default(false)->after('gibberish_code');
            $table->timestamp('gibberish_until')->nullable()->after('is_frozen');

            $table->timestamp('freeze_code_used_at')->nullable()->after('gibberish_until');
            $table->string('freeze_code_used_by')->nullable()->after('freeze_code_used_at');
            $table->string('freeze_code_used_ip', 45)->nullable()->after('freeze_code_used_by');

            $table->timestamp('delete_code_used_at')->nullable()->after('freeze_code_used_ip');
            $table->string('delete_code_used_by')->nullable()->after('delete_code_used_at');
            $table->string('delete_code_used_ip', 45)->nullable()->after('delete_code_used_by');

            $table->timestamp('gibberish_code_used_at')->nullable()->after('delete_code_used_ip');
            $table->string('gibberish_code_used_by')->nullable()->after('gibberish_code_used_at');
            $table->string('gibberish_code_used_ip', 45)->nullable()->after('gibberish_code_used_by');
        });
    }

    public function down(): void
    {
        Schema::table('sayit_chat_rooms', function (Blueprint $table) {
            $table->dropColumn([
                'freeze_code',
                'delete_code',
                'gibberish_code',
                'is_frozen',
                'gibberish_until',
                'freeze_code_used_at',
                'freeze_code_used_by',
                'freeze_code_used_ip',
                'delete_code_used_at',
                'delete_code_used_by',
                'delete_code_used_ip',
                'gibberish_code_used_at',
                'gibberish_code_used_by',
                'gibberish_code_used_ip',
            ]);
        });
    }
};
