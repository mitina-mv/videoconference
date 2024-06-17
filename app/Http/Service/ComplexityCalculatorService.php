<?php

namespace App\Http\Service;

use App\Models\Answerlog;
use App\Models\Question;

class ComplexityCalculatorService
{
    public function calculateThemeComplexity($themeId)
    {
        $questions = Question::where('theme_id', $themeId)->get(['id', 'mark']);
        $questionIds = $questions->pluck('id');

        $answerLogs = Answerlog::whereIn('question_id', $questionIds)->get(['question_id', 'mark']);

        $sumStudentMarks = $answerLogs->sum('mark');

        if($sumStudentMarks == 0 || $answerLogs->count() == 0) {
            throw new \Exception('Нет данных для расчета');
        }

        $answerCounts = $answerLogs->groupBy('question_id')->map->count();
        $sumQuestionMarks = $questions->reduce(function ($carry, $question) use ($answerCounts) {
            return $carry + ($question->mark * ($answerCounts[$question->id] ?? 0));
        }, 0);

        $P_correct = round($sumStudentMarks / $sumQuestionMarks, 2) * 100;

        return $P_correct;
    }

    public function calculateQuestionComplexity($questionId)
    {
        $answerLogs = Answerlog::where('question_id', $questionId)->get(['mark']);

        $sumStudentMarks = $answerLogs->sum('mark');
        $totalAnswers = $answerLogs->count();
        $questionMark = Question::find($questionId)->mark;

        if($sumStudentMarks == 0) {
            throw new \Exception('Нет данных для расчета');
        }

        $P_i = round(($sumStudentMarks / ($totalAnswers * $questionMark)), 2) * 100;

        return $P_i;
    }
}