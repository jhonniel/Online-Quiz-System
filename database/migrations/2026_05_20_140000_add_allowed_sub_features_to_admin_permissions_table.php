<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->json('allowed_content_features')->nullable()->after('content_management');
            $table->json('allowed_employee_features')->nullable()->after('allowed_employee_departments');
            $table->json('allowed_student_features')->nullable()->after('allowed_student_departments');
            $table->json('allowed_hiring_features')->nullable()->after('allowed_positions');
            $table->json('allowed_communication_features')->nullable()->after('communication');
            $table->json('allowed_subscription_features')->nullable()->after('billing');
            $table->json('allowed_user_management_features')->nullable()->after('user_management');
            $table->json('allowed_system_features')->nullable()->after('system');
        });
    }

    public function down(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->dropColumn([
                'allowed_content_features',
                'allowed_employee_features',
                'allowed_student_features',
                'allowed_hiring_features',
                'allowed_communication_features',
                'allowed_subscription_features',
                'allowed_user_management_features',
                'allowed_system_features',
            ]);
        });
    }
};
