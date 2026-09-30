<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->boolean('is_public')->default(false)->after('description');
        });

        DB::table('files')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(200, function ($files) {
                foreach ($files as $file) {
                    DB::table('files')
                        ->where('id', $file->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'is_public']);
        });
    }
};
