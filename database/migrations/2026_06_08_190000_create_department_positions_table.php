<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['department_id', 'name']);
        });

        if (Schema::hasColumn('departments', 'position')) {
            $departments = DB::table('departments')
                ->whereNotNull('position')
                ->where('position', '!=', '')
                ->get(['id', 'position']);

            foreach ($departments as $department) {
                DB::table('department_positions')->insert([
                    'department_id' => $department->id,
                    'name' => trim((string) $department->position),
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'department_position_id')) {
                $table->foreignId('department_position_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('department_positions')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasTable('department_positions')) {
            $users = DB::table('users')
                ->whereNotNull('department_id')
                ->where('role', 'employee')
                ->get(['id', 'department_id']);

            foreach ($users as $user) {
                $positionId = DB::table('department_positions')
                    ->where('department_id', $user->department_id)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->value('id');

                if ($positionId !== null) {
                    DB::table('users')->where('id', $user->id)->update([
                        'department_position_id' => $positionId,
                    ]);
                }
            }
        }

        if (Schema::hasColumn('departments', 'position')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('departments', 'position')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('position')->nullable()->after('name');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'department_position_id')) {
                $table->dropConstrainedForeignId('department_position_id');
            }
        });

        Schema::dropIfExists('department_positions');
    }
};
