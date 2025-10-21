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
        // Get all questions that have answers but no options in the questions table
        $questions = DB::table('questions')
            ->whereNull('option_a')
            ->whereNull('option_b')
            ->whereNull('option_c')
            ->whereNull('option_d')
            ->whereNull('correct_answer')
            ->where('question_type', 'multiple_choice')
            ->get();

        foreach ($questions as $question) {
            // Get answers for this question ordered by order field
            $answers = DB::table('answers')
                ->where('question_id', $question->id)
                ->orderBy('order')
                ->get();

            if ($answers->count() >= 4) {
                // Map answers to options
                $optionA = $answers->get(0)->answer_text ?? null;
                $optionB = $answers->get(1)->answer_text ?? null;
                $optionC = $answers->get(2)->answer_text ?? null;
                $optionD = $answers->get(3)->answer_text ?? null;

                // Find the correct answer
                $correctAnswer = null;
                foreach ($answers as $index => $answer) {
                    if ($answer->is_correct) {
                        $correctAnswer = chr(65 + $index); // A, B, C, D
                        break;
                    }
                }

                // Update the question with the options
                DB::table('questions')
                    ->where('id', $question->id)
                    ->update([
                        'option_a' => $optionA,
                        'option_b' => $optionB,
                        'option_c' => $optionC,
                        'option_d' => $optionD,
                        'correct_answer' => $correctAnswer,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the options from questions table
        DB::table('questions')
            ->whereNotNull('option_a')
            ->update([
                'option_a' => null,
                'option_b' => null,
                'option_c' => null,
                'option_d' => null,
                'correct_answer' => null,
            ]);
    }
};
