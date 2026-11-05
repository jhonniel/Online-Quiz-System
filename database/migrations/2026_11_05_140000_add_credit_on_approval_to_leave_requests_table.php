<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Overtime filed by an admin (or staff with admin access) counts toward the overtime balance
     * as soon as it is approved, even when its date is still in the future.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('leave_requests', 'credit_on_approval')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->boolean('credit_on_approval')->default(false)->after('status');
            });
        }

        DB::table('leave_requests')
            ->where('type', 'overtime')
            ->whereIn('id', DB::table('leave_request_logs')->where('action', 'filed_by_admin')->select('leave_request_id'))
            ->update(['credit_on_approval' => true]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('leave_requests', 'credit_on_approval')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('credit_on_approval');
        });
    }
};
