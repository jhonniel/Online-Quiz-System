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
        Schema::create('other_subscription_providers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('other_subscriptions', function (Blueprint $table) {
            $table->foreignId('other_subscription_provider_id')
                ->nullable()
                ->after('provider')
                ->constrained('other_subscription_providers')
                ->nullOnDelete();
        });

        if (Schema::hasTable('other_subscriptions')) {
            $names = DB::table('other_subscriptions')
                ->whereNotNull('provider')
                ->where('provider', '!=', '')
                ->distinct()
                ->pluck('provider');

            foreach ($names as $name) {
                $trimmed = trim((string) $name);
                if ($trimmed === '') {
                    continue;
                }

                $providerId = DB::table('other_subscription_providers')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'name' => $trimmed,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('other_subscriptions')
                    ->where('provider', $name)
                    ->update(['other_subscription_provider_id' => $providerId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('other_subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('other_subscription_provider_id');
        });

        Schema::dropIfExists('other_subscription_providers');
    }
};
