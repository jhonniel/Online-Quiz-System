<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\QuizAssignment;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => false, // Disabled user
            'email_verified_at' => now(),
        ]);

        // Create test quiz
        $quiz = Quiz::create([
            'title' => 'Laravel Basics Quiz',
            'description' => 'Test your knowledge of Laravel framework basics',
            'quiz_code' => 'LARAVEL1',
            'time_limit' => 30, // 30 minutes
            'total_questions' => 3,
            'is_active' => true,
            'created_by' => 1, // Admin user
        ]);

        // Create questions and answers
        $question1 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'What is Laravel?',
            'question_type' => 'multiple_choice',
            'points' => 10,
            'order' => 1,
        ]);

        Answer::create([
            'question_id' => $question1->id,
            'answer_text' => 'A PHP framework',
            'is_correct' => true,
            'order' => 1,
        ]);

        Answer::create([
            'question_id' => $question1->id,
            'answer_text' => 'A JavaScript library',
            'is_correct' => false,
            'order' => 2,
        ]);

        Answer::create([
            'question_id' => $question1->id,
            'answer_text' => 'A database',
            'is_correct' => false,
            'order' => 3,
        ]);

        $question2 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Laravel uses the MVC pattern.',
            'question_type' => 'true_false',
            'points' => 5,
            'order' => 2,
        ]);

        Answer::create([
            'question_id' => $question2->id,
            'answer_text' => 'True',
            'is_correct' => true,
            'order' => 1,
        ]);

        Answer::create([
            'question_id' => $question2->id,
            'answer_text' => 'False',
            'is_correct' => false,
            'order' => 2,
        ]);

        $question3 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'What is the command to create a new Laravel project?',
            'question_type' => 'text',
            'points' => 15,
            'order' => 3,
        ]);

        // Create another quiz
        $quiz2 = Quiz::create([
            'title' => 'PHP Fundamentals',
            'description' => 'Basic PHP programming concepts',
            'quiz_code' => 'PHP101',
            'time_limit' => 20,
            'total_questions' => 2,
            'is_active' => true,
            'created_by' => 1,
        ]);

        $question4 = Question::create([
            'quiz_id' => $quiz2->id,
            'question_text' => 'What does PHP stand for?',
            'question_type' => 'multiple_choice',
            'points' => 10,
            'order' => 1,
        ]);

        Answer::create([
            'question_id' => $question4->id,
            'answer_text' => 'PHP: Hypertext Preprocessor',
            'is_correct' => true,
            'order' => 1,
        ]);

        Answer::create([
            'question_id' => $question4->id,
            'answer_text' => 'Personal Home Page',
            'is_correct' => false,
            'order' => 2,
        ]);

        $question5 = Question::create([
            'quiz_id' => $quiz2->id,
            'question_text' => 'PHP is a server-side scripting language.',
            'question_type' => 'true_false',
            'points' => 10,
            'order' => 2,
        ]);

        Answer::create([
            'question_id' => $question5->id,
            'answer_text' => 'True',
            'is_correct' => true,
            'order' => 1,
        ]);

        Answer::create([
            'question_id' => $question5->id,
            'answer_text' => 'False',
            'is_correct' => false,
            'order' => 2,
        ]);

        // Assign quizzes to users
        QuizAssignment::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user1->id,
            'assigned_at' => now(),
            'due_date' => now()->addDays(7),
            'is_completed' => false,
        ]);

        QuizAssignment::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user2->id,
            'assigned_at' => now(),
            'due_date' => now()->addDays(7),
            'is_completed' => false,
        ]);

        QuizAssignment::create([
            'quiz_id' => $quiz2->id,
            'user_id' => $user1->id,
            'assigned_at' => now(),
            'due_date' => now()->addDays(5),
            'is_completed' => false,
        ]);

        $this->command->info('Test data created successfully!');
        $this->command->info('Test users:');
        $this->command->info('- john@example.com (password: password)');
        $this->command->info('- jane@example.com (password: password)');
        $this->command->info('- bob@example.com (password: password) - DISABLED');
        $this->command->info('Admin: admin@quiz.com (password: password)');
        $this->command->info('Quiz codes: LARAVEL1, PHP101');
    }
}
