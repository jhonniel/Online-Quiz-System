<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('paypals_settlements');
        Schema::dropIfExists('paypals_item_shares');
        Schema::dropIfExists('paypals_bill_items');
        Schema::dropIfExists('paypals_bills');
        Schema::dropIfExists('paypals_group_members');
        Schema::dropIfExists('paypals_groups');
        Schema::dropIfExists('paypals_members');
        Schema::dropIfExists('paypals_invites');

        if (Schema::hasTable('admin_permissions')) {
            Schema::table('admin_permissions', function (Blueprint $table) {
                if (Schema::hasColumn('admin_permissions', 'allowed_paypals_features')) {
                    $table->dropColumn('allowed_paypals_features');
                }
                if (Schema::hasColumn('admin_permissions', 'paypals')) {
                    $table->dropColumn('paypals');
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty — PayPals was removed from this project.
    }
};
