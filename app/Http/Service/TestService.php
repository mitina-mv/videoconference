<?php

namespace App\Services;

use App\Models\Testlog;
use App\Models\Answerlog;
use App\Models\Answer;
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

            foreach ($answers as $questionId => $answerData) {
                $answerlog = Answerlog::where('testlog_id', $testlog->id)
                    ->where('question_id', $questionId)
                    ->first();

                if (!$answerlog) {
                    throw new Exception('Не найдено задание');
                }

                if (is_array($answerData['value'])) {
                    $answerlog->answers()->detach();
                    foreach ($answerData['value'] as $answerId) {
                        $answerlog->answers()->attach($answerId, [
                            'is_correct' => $this->checkAnswerCorrectness($answerId),
                            'text' => null
                        ]);
                    }
                } else {
                    $answerId = $answerData['value'];
                    $answerlog->answers()->detach();
                    $answerlog->answers()->attach($answerId, [
                        'is_correct' => $this->checkAnswerCorrectness($answerId),
                        'text' => null
                    ]);
                }
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function checkAnswerCorrectness($answerId)
    {
        $answer = Answer::find($answerId);
        return $answer ? $answer->status : false;
    }
}
