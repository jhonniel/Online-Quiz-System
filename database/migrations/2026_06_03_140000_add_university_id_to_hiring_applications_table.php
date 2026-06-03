<?php

use App\Models\HiringApplication;
use App\Models\University;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hiring_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('hiring_applications', 'university_id')) {
                $table->foreignId('university_id')
                    ->nullable()
                    ->after('school')
                    ->constrained('universities')
                    ->nullOnDelete();
            }
        });

        HiringApplication::query()
            ->whereNotNull('school')
            ->whereNull('university_id')
            ->orderBy('id')
            ->each(function (HiringApplication $application) {
                $universityId = University::resolveIdFromSchoolLabel($application->school);
                if ($universityId) {
                    $application->update(['university_id' => $universityId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('hiring_applications', function (Blueprint $table) {
            if (Schema::hasColumn('hiring_applications', 'university_id')) {
                $table->dropConstrainedForeignId('university_id');
            }
        });
    }
};
