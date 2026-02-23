<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class InstallLinkedTables extends Command
{
    protected $signature = 'linked-tables:install';
    protected $description = 'Create linked_accounts, starlinks, and omadas tables if they do not exist (bypasses migration runner).';

    public function handle(): int
    {
        $connection = config('database.default');

        if (!Schema::connection($connection)->hasTable('linked_accounts')) {
            $this->info('Creating linked_accounts table...');
            Schema::connection($connection)->create('linked_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('email')->index();
                $table->string('name')->nullable();
                $table->string('provider')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
            $this->info('Created linked_accounts.');
        } else {
            $this->comment('linked_accounts already exists.');
        }

        if (!Schema::connection($connection)->hasTable('starlinks')) {
            $this->info('Creating starlinks table...');
            Schema::connection($connection)->create('starlinks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('linked_account_id')->nullable();
                $table->string('account_linked_email')->nullable()->index();
                $table->string('starlink_id')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('kit_number')->nullable();
                $table->string('router_id')->nullable();
                $table->string('ssid')->nullable();
                $table->string('wifi_password')->nullable();
                $table->string('office_location')->nullable();
                $table->date('start_date')->nullable();
                $table->string('po_no')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('plan')->nullable();
                $table->string('status', 50)->nullable();
                $table->string('end_user_email')->nullable();
                $table->timestamps();
                $table->foreign('linked_account_id')->references('id')->on('linked_accounts')->nullOnDelete();
            });
            $this->info('Created starlinks.');
        } else {
            $this->comment('starlinks already exists.');
        }

        if (!Schema::connection($connection)->hasTable('omadas')) {
            $this->info('Creating omadas table...');
            Schema::connection($connection)->create('omadas', function (Blueprint $table) {
                $table->id();
                $table->string('account_linked_email')->nullable()->index();
                $table->string('site')->nullable();
                $table->string('office')->nullable();
                $table->string('type')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('mac_address')->nullable();
                $table->string('license')->nullable();
                $table->date('license_expiration')->nullable();
                $table->timestamps();
            });
            $this->info('Created omadas.');
        } else {
            $this->comment('omadas already exists.');
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
