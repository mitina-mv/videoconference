<?php

namespace App\Http\Controllers;

use App\Models\Answerlog;
use App\Models\Question;
use App\Models\Testlog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MyAssignmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $disciplines = Testlog::where('user_id', $user->id)
            ->with('assignment.test.theme.discipline')
            ->get()
            ->pluck('assignment.test.theme.discipline')
            ->unique()
            ->toArray();

        array_unshift($disciplines, [
            'name' => 'Все дисциплины',
            'id' => '__all'
        ]);

        return Inertia::render('Assignment/My', [
            'disciplines' => $disciplines
        ]);
    }

    public function testing(Request $request)
    {
        $user = Auth::user();
        $testlog = Testlog::where('id', $request->testlog_id)
            ->where('user_id', $user->id)
            ->with(['assignment', 'assignment.test', 'assignment.user'])
            ->first();

        if(!$testlog) {
            return $this->renderError('Это тестирование назначено не вам.');
        }
        
        if(!$testlog->assignment->is_active || $testlog->mark) {
            return $this->renderError('Этот тест недоступен для прохождения');
        }

        $test = $testlog->assignment->test;
        $testSettings = $test->settings;

        if($testSettings['fixed_questions']) {
            $questions = $testSettings['question_ids'];
            if($testSettings['is_random']) {
                shuffle($questions);
            }
        } else {
            $questions = Question::where('theme_id', $test->theme_id)
                ->where(function($query) use ($test) {
                    $query->where('is_private', false)
                        ->orWhere(function($query) use ($test) {
                            $query->where('is_private', true)
                                    ->where('user_id', $test->user_id);
                        });
                })
                ->whereHas('correct_answers')
                ->inRandomOrder()
                ->select('id')
                ->limit($testSettings['count_questions'])
                ->get()
                ->pluck('id')
                ->toArray();

            if($questions < $testSettings['count_questions']) {
                return $this->renderError('Не удалось получить тестовое задание по причине нехватки или отсуствия вопросов по теме. Обратитесь к преподавателю, который назначил тестирование.');
            }
        }

        if(empty($questions)) {
            return $this->renderError('Не удалось получить тестовое задание по причине отсуствия вопросов по теме. Обратитесь к преподавателю, который назначил тестирование.');
        }

        try {
            $answerlogs = [];
            foreach ($questions as $id) {
                $al = Answerlog::where('question_id', $id)
                    ->where('testlog_id', $testlog->id)
                    ->first();

                if($al) {
                    $answerlogs[$id] = $al->id;
                } else {
                    $answerlogs[$id] = Answerlog::create([
                        'question_id' => $id,
                        'testlog_id' => $testlog->id,
                    ])->id;
                }
            }
        } catch (Exception $e) {
            return $this->renderError('Не удалось назначить тестирование.');
        }

        $questionsPublish = Question::whereIn('id', $questions)
            ->with(['answers' => function ($query) {
                $query->select('id', 'question_id', 'name')->inRandomOrder();
            }])
            ->select('id', 'text', 'type', 'path')
            ->get()
            ->each(function($row) {
                $row->setHidden(['correct_answers']);
            });

        return Inertia::render('Assignment/Testing', [
            'questions' => $questionsPublish,
            'settings' => $testSettings,
            'answerlogs' => $answerlogs,
            'testlog_id' => $testlog->id,
        ]);
    }

    private function renderError(string $message)
    {
        return Inertia::render('Assignment/Testing', [
            'error' => $message,
        ]);
    }
}
