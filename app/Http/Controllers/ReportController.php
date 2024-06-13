<?php
namespace App\Http\Controllers;

use App\Models\Testlog;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function student(string $testlog_id)
    {
        $testlog = Testlog::where('id', $testlog_id)
            ->with([
                'assignment',
                'assignment.videoconference',
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
            'vc' => $testlog->assignment->videoconference,
            'backLink' => 'assignments.my'
        ]);
    }

    private function renderError(string $page, string $message)
    {
        return Inertia::render($page, [
            'error' => $message,
        ]);
    }
}
