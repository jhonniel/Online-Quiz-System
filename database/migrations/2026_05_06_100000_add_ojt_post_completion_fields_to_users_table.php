<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'ojt_requirement_met_at')) {
                $table->timestamp('ojt_requirement_met_at')->nullable()->after('student_terminated');
            }
            if (! Schema::hasColumn('users', 'ojt_completion_congratulations_sent_at')) {
                $table->timestamp('ojt_completion_congratulations_sent_at')->nullable()->after('ojt_requirement_met_at');
            }
            if (! Schema::hasColumn('users', 'ojt_post_completion_grace_closed_at')) {
                $table->timestamp('ojt_post_completion_grace_closed_at')->nullable()->after('ojt_completion_congratulations_sent_at');
            }
            if (! Schema::hasColumn('users', 'ojt_account_disabled_notice_sent_at')) {
                $table->timestamp('ojt_account_disabled_notice_sent_at')->nullable()->after('ojt_post_completion_grace_closed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'ojt_account_disabled_notice_sent_at',
                'ojt_post_completion_grace_closed_at',
                'ojt_completion_congratulations_sent_at',
                'ojt_requirement_met_at',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
