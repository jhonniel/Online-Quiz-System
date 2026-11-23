<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('billing_type', 20)->default('monthly'); // monthly|annual
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('PHP');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('auto_renew')->default(false);
            $table->unsignedTinyInteger('notify_days_before')->default(7);
            $table->text('notes')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->date('last_notified_for_ends_on')->nullable();
            $table->timestamps();

            $table->index(['ends_on', 'auto_renew']);
            $table->index('billing_type');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_subscriptions');
    }
};
