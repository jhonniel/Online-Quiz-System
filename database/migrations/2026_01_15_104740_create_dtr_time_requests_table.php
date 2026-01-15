<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        Schema::create('dtr_time_requests', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('hours', 5, 2); // Hours requested (e.g., 8.00, 4.50)
            $table->text('remarks')->nullable(); // Optional notes from student
            
            // Use string for PostgreSQL, enum for MySQL/SQLite
            if ($driver === 'pgsql') {
                $table->string('status', 20)->default('pending');
            } else {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            }
            
            $table->text('admin_notes')->nullable(); // Admin notes when approving/rejecting
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('date');
        });
        
        // Add check constraint for PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE dtr_time_requests ADD CONSTRAINT dtr_time_requests_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Drop check constraint for PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE dtr_time_requests DROP CONSTRAINT IF EXISTS dtr_time_requests_status_check');
        }
        
        Schema::dropIfExists('dtr_time_requests');
    }
};
