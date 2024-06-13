<?php
namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Testlog;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function student(string $testlog_id)
    {
        $user = request()->user();
        $testlog = Testlog::where('id', $testlog_id)
            ->with([
                'assignment',
                'assignment.user', 
                'answerlogs', 
                'answerlogs.answers', 
                'answerlogs.question'
            ])
            ->first();

        if(!$testlog) {
            return $this->renderError('Reports/Student', 'Не найдено тестирование');
        }

        if(!$testlog->mark) {
            return $this->renderError('Reports/Student', 'Вы еще не проходили этот тест');
        }

        if($testlog->user_id != $user->id) {
            return $this->renderError('Reports/Student', 'Вы не можете просматривать этот отчет');
        }

        $questions = [];
        foreach($testlog->answerlogs->toArray() as $q)
        {
            $item = [
                'mark' => $q['mark'],
                'total_mark' => $q['question']['mark'],
                'question' => $q['question']['text'],
                'answers' => empty($q['answers']) ? null : array_column($q['answers'], 'history')
            ];
            if($q['question']['type'] == 'text'
                && empty($q['answers'])
                && $testlog->uncorrect_answers
            ) {
                if(isset($testlog->uncorrect_answers[$q['question']['id']])) {
                    $item['answers'][] = [
                        "is_correct" => false,
                        "text" => $testlog->uncorrect_answers[$q['question']['id']]
                    ];
                }
            }
            $questions[] = $item;
        }

        return Inertia::render('Reports/Student', [
            'test' => $testlog->assignment->test()->select('name')->first(),
            'testlog' => $testlog,
            'user' => request()->user(),
            'questions' => $questions,
            'backLink' => 'assignments.my'
        ]);
    }

    public function assignment(string $assignment_id)
    {
        $assignment = Assignment::where('id', $assignment_id)
            ->with(['testlogs.user.studgroup', 'test'])
            ->first();
        $user = request()->user();

        /* if (!$assignment) {
            return $this->renderError('Reports/Assignment', 'Назначение не найдено');
        }

        if($assignment->user_id != $user->id) {
            return $this->renderError('Reports/Assignment', 'Вы не можете просматривать этот отчет');
        } */

        $groupedData = [];
        $marks = [];

        foreach ($assignment->testlogs as $testlog)
        {
            $group = $testlog->user->studgroup->name ?? 'Без группы';

            if (!isset($groupedData[$group])) {
                $groupedData[$group] = [];
            }

            $groupedData[$group][] = [
                'full_name' => $testlog->user->full_name,
                'mark' => $testlog->mark == null ? 'Нет' : $testlog->mark
            ];

            if(!empty($testlog->mark)) {
                $marks[] = $testlog->mark;
            }
        }

        if(count($marks) == 0) {
            return $this->renderError('Reports/Assignment', 'Студенты еще не прошли это текстирование.');
        }

        // метрики
        $avgMark = round(array_sum($marks) / count($marks), 2);
        $deviationMarks = array_map(fn($mark) => pow($mark - $avgMark, 2), $marks);
        $deviation = round( sqrt( array_sum($deviationMarks) / count($marks) ), 2);

        $theme = $assignment->test->theme()->with('discipline')->first();

        return Inertia::render('Reports/Assignment', [
            'test' => [
                'name' => $assignment->test->name,
                'discipline' => $theme->discipline->name,
                'theme' => $theme->name
            ],
            'assignment' =>  $assignment,
            'test_settings' => $assignment->test->settings,
            'groups' => $groupedData,
            'user' => request()->user(),
            'vc' => $assignment->videoconference()->first(),
            'metrics' => [
                'avg_mark' => $avgMark,
                'deviation_mark' => $deviation
            ],
            'backLink' => 'assignments.index'
        ]);
    }


    private function renderError(string $page, string $message)
    {
        return Inertia::render($page, [
            'error' => $message,
        ]);
    }
}
