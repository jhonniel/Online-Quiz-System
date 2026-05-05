<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'student_rules_notice_message')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('student_rules_notice_message')->nullable();
            });
        }

        if (Schema::hasColumn('users', 'student_rules_marquee_text')
            && Schema::hasColumn('users', 'student_rules_notice_message')) {
            foreach (DB::table('users')->whereNotNull('student_rules_marquee_text')->cursor() as $row) {
                $src = trim((string) ($row->student_rules_marquee_text ?? ''));
                if ($src === '') {
                    continue;
                }
                $current = trim((string) (DB::table('users')->where('id', $row->id)->value('student_rules_notice_message') ?? ''));
                if ($current !== '') {
                    continue;
                }
                DB::table('users')->where('id', $row->id)->update([
                    'student_rules_notice_message' => $row->student_rules_marquee_text,
                ]);
            }
        }

        if (Schema::hasColumn('users', 'student_rules_marquee_text')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('student_rules_marquee_text');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'student_rules_marquee_text')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('student_rules_marquee_text')->nullable();
            });
        }

        if (Schema::hasColumn('users', 'student_rules_notice_message')
            && Schema::hasColumn('users', 'student_rules_marquee_text')) {
            foreach (DB::table('users')->whereNotNull('student_rules_notice_message')->cursor() as $row) {
                $src = trim((string) ($row->student_rules_notice_message ?? ''));
                if ($src === '') {
                    continue;
                }
                DB::table('users')->where('id', $row->id)->update([
                    'student_rules_marquee_text' => $row->student_rules_notice_message,
                ]);
            }
        }

        if (Schema::hasColumn('users', 'student_rules_notice_message')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('student_rules_notice_message');
            });
        }
    }
};
