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
        // SQLite doesn't support ALTER COLUMN with CHECK constraints directly
        // We need to recreate the table with the updated constraint
        DB::statement('
            CREATE TABLE quiz_attempt_history_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                quiz_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                attempt_number INTEGER NOT NULL,
                score INTEGER NOT NULL,
                total_questions INTEGER NOT NULL,
                correct_answers INTEGER NOT NULL,
                time_taken_seconds INTEGER,
                started_at DATETIME NOT NULL,
                completed_at DATETIME NOT NULL,
                status VARCHAR CHECK (status IN (\'completed\', \'time_expired\', \'cancelled\', \'partial\')) NOT NULL DEFAULT \'completed\',
                answers TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ');

        // Copy data from old table to new table
        DB::statement('INSERT INTO quiz_attempt_history_new SELECT * FROM quiz_attempt_history');

        // Drop old table and rename new table
        DB::statement('DROP TABLE quiz_attempt_history');
        DB::statement('ALTER TABLE quiz_attempt_history_new RENAME TO quiz_attempt_history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the table with the original constraint
        DB::statement('
            CREATE TABLE quiz_attempt_history_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                quiz_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                attempt_number INTEGER NOT NULL,
                score INTEGER NOT NULL,
                total_questions INTEGER NOT NULL,
                correct_answers INTEGER NOT NULL,
                time_taken_seconds INTEGER,
                started_at DATETIME NOT NULL,
                completed_at DATETIME NOT NULL,
                status VARCHAR CHECK (status IN (\'completed\', \'time_expired\', \'cancelled\')) NOT NULL DEFAULT \'completed\',
                answers TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ');

        // Copy data from current table to old table (excluding partial status)
        DB::statement('INSERT INTO quiz_attempt_history_old SELECT * FROM quiz_attempt_history WHERE status != \'partial\'');

        // Drop current table and rename old table
        DB::statement('DROP TABLE quiz_attempt_history');
        DB::statement('ALTER TABLE quiz_attempt_history_old RENAME TO quiz_attempt_history');
    }
};
