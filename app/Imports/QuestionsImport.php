<?php

namespace App\Imports;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class QuestionsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    private $quizId;
    private $questionOrder = 1;

    public function __construct($quizId)
    {
        $this->quizId = $quizId;
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();

        // Skip header row
        if (empty($rowData['question_text'])) {
            return;
        }

        try {
            DB::beginTransaction();

            // Create the question
            $question = Question::create([
                'quiz_id' => $this->quizId,
                'question_text' => $rowData['question_text'],
                'question_type' => $this->normalizeQuestionType($rowData['question_type']),
                'points' => (int) ($rowData['points'] ?? 1),
                'order' => $this->questionOrder++,
            ]);

            // Create answers based on question type
            $this->createAnswers($question, $rowData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error importing question: " . $e->getMessage());
        }
    }

    private function normalizeQuestionType($type)
    {
        $type = strtolower(trim($type));

        switch ($type) {
            case 'multiple choice':
            case 'multiple_choice':
            case 'mc':
                return 'multiple_choice';
            case 'true false':
            case 'true_false':
            case 'tf':
                return 'true_false';
            case 'text':
            case 'essay':
            case 'short answer':
                return 'text';
            default:
                return 'multiple_choice'; // Default fallback
        }
    }

    private function createAnswers(Question $question, array $rowData)
    {
        $questionType = $question->question_type;
        $correctAnswer = (int) ($rowData['correct_answer'] ?? 1);

        if ($questionType === 'multiple_choice') {
            // Create multiple choice answers
            for ($i = 1; $i <= 4; $i++) {
                $answerText = $rowData["answer_{$i}"] ?? '';
                if (!empty($answerText)) {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answerText,
                        'is_correct' => ($i === $correctAnswer),
                        'order' => $i,
                    ]);
                }
            }
        } elseif ($questionType === 'true_false') {
            // Create true/false answers
            $answers = ['True', 'False'];
            foreach ($answers as $index => $answerText) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerText,
                    'is_correct' => (($index + 1) === $correctAnswer),
                    'order' => $index + 1,
                ]);
            }
        }
        // For text questions, no answers are created as they are open-ended
    }
}
