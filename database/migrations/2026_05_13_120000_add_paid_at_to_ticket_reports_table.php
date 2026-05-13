<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('amount_paid');
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            DB::table('ticket_reports')
                ->where('payment_status', 'paid')
                ->whereNull('paid_at')
                ->update(['paid_at' => DB::raw('COALESCE(updated_at, created_at)')]);
        } else {
            DB::table('ticket_reports')
                ->where('payment_status', 'paid')
                ->whereNull('paid_at')
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        $ts = $row->updated_at ?? $row->created_at;
                        DB::table('ticket_reports')->where('id', $row->id)->update(['paid_at' => $ts]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
