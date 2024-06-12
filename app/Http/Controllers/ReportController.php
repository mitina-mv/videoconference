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

        // dd($testlog->assignment->toArray());

        return Inertia::render('Reports/Student', [
            'test' => $testlog->assignment->test()->select('name')->first(),
            'testlog' => $testlog,
            'user' => request()->user(),
            'questions' => $testlog->answerlogs
        ]);
    }

    private function renderError(string $page, string $message)
    {
        return Inertia::render($page, [
            'error' => $message,
        ]);
    }
}
