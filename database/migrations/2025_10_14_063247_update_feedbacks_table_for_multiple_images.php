<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Rename column and change type
            DB::statement('ALTER TABLE feedbacks RENAME COLUMN image TO images');
            DB::statement('ALTER TABLE feedbacks ALTER COLUMN images TYPE JSON USING CASE WHEN images IS NULL THEN NULL ELSE json_build_array(images) END');
        } elseif ($driver === 'sqlite') {
            // SQLite: Need to recreate the table or use a workaround
            // For SQLite, we'll add a new column and migrate data
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->json('images')->nullable();
            });
            // Migrate existing single image to images array
            DB::statement("UPDATE feedbacks SET images = json_array(image) WHERE image IS NOT NULL");
            // Drop old column
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        } else {
            // MySQL
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->renameColumn('image', 'images');
            });
            DB::statement('ALTER TABLE feedbacks MODIFY COLUMN images JSON');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Change type back and rename
            DB::statement('ALTER TABLE feedbacks ALTER COLUMN images TYPE VARCHAR(255) USING images::text');
            DB::statement('ALTER TABLE feedbacks RENAME COLUMN images TO image');
        } elseif ($driver === 'sqlite') {
            // SQLite: Add back single image column
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->string('image')->nullable();
            });
            // Migrate first image back
            DB::statement("UPDATE feedbacks SET image = json_extract(images, '$[0]') WHERE images IS NOT NULL");
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->dropColumn('images');
            });
        } else {
            // MySQL
            DB::statement('ALTER TABLE feedbacks MODIFY COLUMN images VARCHAR(255)');
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->renameColumn('images', 'image');
            });
        }
    }
};
