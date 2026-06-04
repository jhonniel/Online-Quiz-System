<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_file_requests')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->upSqlite();
        } else {
            $this->upDefault();
        }
    }

    private function upDefault(): void
    {
        Schema::table('employee_file_requests', function (Blueprint $table) {
            $table->dropForeign(['employee_file_template_id']);
        });

        Schema::table('employee_file_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_file_template_id')->nullable()->change();
            if (! Schema::hasColumn('employee_file_requests', 'original_filename')) {
                $table->string('original_filename')->nullable()->after('title');
            }
            if (! Schema::hasColumn('employee_file_requests', 'mime_type')) {
                $table->string('mime_type', 127)->nullable()->after('original_filename');
            }
        });

        Schema::table('employee_file_requests', function (Blueprint $table) {
            $table->foreign('employee_file_template_id')
                ->references('id')
                ->on('employee_file_templates')
                ->nullOnDelete();
        });
    }

    private function upSqlite(): void
    {
        if (! Schema::hasColumn('employee_file_requests', 'original_filename')) {
            Schema::table('employee_file_requests', function (Blueprint $table) {
                $table->string('original_filename')->nullable();
                $table->string('mime_type', 127)->nullable();
            });
        }

        DB::statement('PRAGMA foreign_keys=OFF');

        Schema::create('employee_file_requests_new', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_file_template_id')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 127)->nullable();
            $table->json('field_values')->nullable();
            $table->longText('rendered_html')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        $hasOriginal = Schema::hasColumn('employee_file_requests', 'original_filename');
        if ($hasOriginal) {
            DB::statement('
                INSERT INTO employee_file_requests_new (
                    id, employee_file_template_id, user_id, generated_by, title,
                    original_filename, mime_type, field_values, rendered_html, pdf_path,
                    created_at, updated_at
                )
                SELECT
                    id, employee_file_template_id, user_id, generated_by, title,
                    original_filename, mime_type, field_values, rendered_html, pdf_path,
                    created_at, updated_at
                FROM employee_file_requests
            ');
        } else {
            DB::statement('
                INSERT INTO employee_file_requests_new (
                    id, employee_file_template_id, user_id, generated_by, title,
                    original_filename, mime_type, field_values, rendered_html, pdf_path,
                    created_at, updated_at
                )
                SELECT
                    id, employee_file_template_id, user_id, generated_by, title,
                    NULL, NULL, field_values, rendered_html, pdf_path,
                    created_at, updated_at
                FROM employee_file_requests
            ');
        }

        Schema::drop('employee_file_requests');
        Schema::rename('employee_file_requests_new', 'employee_file_requests');

        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_file_requests', 'mime_type')) {
            Schema::table('employee_file_requests', function (Blueprint $table) {
                $table->dropColumn(['mime_type', 'original_filename']);
            });
        }
    }
};
