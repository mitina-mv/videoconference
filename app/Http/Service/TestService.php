<?php

namespace App\Http\Service;

use App\Models\Testlog;
use App\Models\Answerlog;
use App\Models\Answer;
use App\Models\Question;
use Exception;
use Illuminate\Support\Facades\DB;

class TestService
{
    public function saveAnswers($testlogId, $answers)
    {
        DB::beginTransaction();

        try {
            $testlog = Testlog::find($testlogId);

            if (!$testlog) {
                throw new Exception('Не найдено тестирование');
            }

            // получение стоимости теста
            $questionIds = array_keys($answers);
            $questionMarks = Question::whereIn('id', $questionIds)
                ->select('mark')
                ->get()
                ->pluck('mark')
                ->toArray();
            $testAmount = array_sum($questionMarks);
            $testMark = 0;
            
            foreach ($answers as $questionId => $answerData)
            {                
                $answerlog = Answerlog::where('id', $answerData['answerlog_id'])
                    ->first();

                if (!$answerlog) {
                    throw new Exception('Не найдено задание');
                }

                if(empty($answerData['value'])) {
                    $answerlog->update(['mark' => 0]);
                    continue;
                }

                $this->processAnswer($answerlog, $answerData['value']);
                
                $this->calculateMark($answerlog);
                $testMark += $answerlog->mark;
            }

            $testlog->update([
                'mark' => round(($testMark / $testAmount) * 100, 2),
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processAnswer($answerlog, $answerData)
    {
        if (is_array($answerData)) {
            $answerlog->answers()->detach();
            foreach ($answerData as $answerId) {
                $this->attachAnswer($answerlog, $answerId);
            }
        } else {
            $answerlog->answers()->detach();
            if (is_integer($answerData)) {
                $this->attachAnswer($answerlog, $answerData);
            } else {
                $this->processTextAnswer($answerlog, $answerData);
            }
        }
    }

    private function attachAnswer($answerlog, $answerId)
    {
        $answer = Answer::find($answerId);
        $answerlog->answers()->attach($answerId, [
            'is_correct' => $answer ? $answer->status : false,
            'text' => $answer->name
        ]);
    }

    private function processTextAnswer($answerlog, $text)
    {
        $answer = Answer::where('question_id', $answerlog->question_id)
            ->where('name', $text)
            ->first();

        if ($answer) {
            $answerlog->answers()->attach($answer->id, [
                'is_correct' => true,
                'text' => $answer->name
            ]);
        } else {
            $this->logUncorrectAnswer($answerlog, $text);
        }
    }

    private function logUncorrectAnswer($answerlog, $text)
    {
        $testlog = $answerlog->testlog;
        $uncorrect = $testlog->uncorrect_answers;
        $uncorrect[$answerlog->question_id] = $text;
        $testlog->update(['uncorrect_answers' => $uncorrect]);
    }

    public function calculateMark($answerlog)
    {
        $question = $answerlog->question;
        $correctAnswersGet = $answerlog->answers()->where('is_correct', true)->count();
        $totalMark = $question->mark;
        $correctAnswers = $question->correct_answers()->count();
        $type = $question->type;

        $score = $totalMark;
        if ($type == 'multiple') {
            $score = round($totalMark / $correctAnswers, 2);
        }

        $mark = $correctAnswersGet * $score;

        $answerlog->update(['mark' => $mark]);
    }
}
