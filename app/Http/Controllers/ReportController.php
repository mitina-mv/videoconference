<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Testlog;
use App\Models\User;
use App\Models\Videoconference;
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

        if (!$testlog) {
            return $this->renderError('Reports/Student', 'Не найдено тестирование');
        }

        if (!$testlog->mark) {
            return $this->renderError('Reports/Student', 'Вы еще не проходили этот тест');
        }

        if ($testlog->user_id != $user->id) {
            return $this->renderError('Reports/Student', 'Вы не можете просматривать этот отчет');
        }

        $questions = [];
        foreach ($testlog->answerlogs->toArray() as $q) {
            $item = [
                'mark' => $q['mark'],
                'total_mark' => $q['question']['mark'],
                'question' => $q['question']['text'],
                'answers' => empty($q['answers']) ? null : array_column($q['answers'], 'history')
            ];
            if (
                $q['question']['type'] == 'text'
                && empty($q['answers'])
                && $testlog->uncorrect_answers
            ) {
                if (isset($testlog->uncorrect_answers[$q['question']['id']])) {
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

        if (!$assignment) {
            return $this->renderError('Reports/Assignment', 'Назначение не найдено');
        }

        if ($assignment->user_id != $user->id) {
            return $this->renderError('Reports/Assignment', 'Вы не можете просматривать этот отчет');
        }

        $groupedData = [];
        $marks = [];

        foreach ($assignment->testlogs as $testlog) {
            $group = $testlog->user->studgroup->name ?? 'Без группы';

            if (!isset($groupedData[$group])) {
                $groupedData[$group] = [];
            }

            $groupedData[$group][] = [
                'full_name' => $testlog->user->full_name,
                'mark' => $testlog->mark == null ? 'Нет' : $testlog->mark
            ];

            if (!empty($testlog->mark)) {
                $marks[] = $testlog->mark;
            }
        }

        if (count($marks) == 0) {
            return $this->renderError('Reports/Assignment', 'Студенты еще не прошли это текстирование.');
        }

        // метрики
        $avgMark = round(array_sum($marks) / count($marks), 2);
        $deviationMarks = array_map(fn ($mark) => pow($mark - $avgMark, 2), $marks);
        $deviation = round(sqrt(array_sum($deviationMarks) / count($marks)), 2);

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

    public function videoconference(string $vc_id)
    {
        $vc = Videoconference::where('id', $vc_id)
            ->with(['studgroups', 'assignment.test', 'assignment.testlogs'])
            ->first();
        $user = request()->user();

        // if (!$vc) {
        //     return $this->renderError('Reports/Videoconference', 'Назначение не найдено');
        // }

        // if($vc->user_id != $user->id) {
        //     return $this->renderError('Reports/Videoconference', 'Вы не можете просматривать этот отчет');
        // }

        // if(!$vc->is_completed) {
        //     return $this->renderError('Reports/Videoconference', 'Эта видеоконференция не состоялась');
        // }

        $metrics = $vc->metrics;
        $test = $vc->assignment->test;

        // подготовка данных о студентах
        if (empty($metrics->students)) {
            return $this->renderError('Reports/Videoconference', 'В этой конференции не участвовал ни один студент, нет данных для отчета.');
        }

        $groupData = [];
        $students = [];
        foreach ($metrics->students as $user_id => $student_actions) {
            $student = User::where('id', $user_id)
                ->first();

            if (empty($student)) {
                continue;
            }

            $group = $student->sg_name ?? 'Без группы';

            if (!isset($groupData[$group])) {
                $groupData[$group] = [];
            }

            // рассчет вовлеченности
            $a = $metrics->count_check == 0 ? 1 : $student_actions['count_check'] / $metrics->count_check;
            $c = $vc->messages == null ? 1 : $student_actions['count_message'] / env('COUNT_MESSAGES_PERFECT');
            $h = $student_actions['count_hand'] / env('COUNT_HAND_PERFECT');

            $students[$user_id] = [
                'full_name' => $student->full_name,
                'engagement' => [
                    'a' => $a,
                    'c' => $c,
                    'h' => $h,
                    'p' => 1,
                ],
                'group' => $group,
                'mark' => '-'
            ];
        }

        if ($vc->assignment && $vc->assignment->testlogs) {
            $marks = [];
            foreach($vc->assignment->testlogs as $testlog)
            {
                if(!isset($students[$testlog->user_id])) {
                    continue;
                }
                
                $students[$testlog->user_id]['mark'] = $testlog->mark ? $testlog->mark : 'Нет';
                $countAnswers = $testlog->answerlogs()->where('mark', '!=', null)->count();
                if($countAnswers > 0) {
                    $p = $test->settings->count_questions / $countAnswers;
                } else {
                    $p = 0;
                }

                if(!empty($testlog->mark)) {
                    $marks[] = $testlog->mark;
                    $p = 0;
                }

                $students[$testlog->user_id]['engagement']['p'] = $p;
            }

            // метрики
            $avgMark = null;
            $deviation = null;
            if(count($marks) > 0) {
                $avgMark = round(array_sum($marks) / count($marks), 2);
                $deviationMarks = array_map(fn ($mark) => pow($mark - $avgMark, 2), $marks);
                $deviation = round(sqrt(array_sum($deviationMarks) / count($marks)), 2);
            }
        }

        foreach($students as &$student)
        {
            $engagement = $student['engagement'];
            $ai = $engagement['a'] * env('THRESHOLD_COUNT_CHECK'); 
            $pi = $engagement['p'] * env('THRESHOLD_COUNT_QUESTION'); 
            $ci = $engagement['c'] * env('THRESHOLD_COUNT_MESSAGE'); 
            $hi = $engagement['h'] * env('THRESHOLD_COUNT_HAND'); 
            $student['engagement'] = round($ai + $pi + $ci + $hi, 2);

            $groupData[$student['group']][] = $student;
        }


        $testResult = null;
        if($vc->assignment) {
            $theme = $vc->assignment->test->theme()->with('discipline')->first();

            $testResult = [
                'name' => $vc->assignment->test->name,
                'discipline' => $theme->discipline->name,
                'theme' => $theme->name,
                'avg_mark' => $avgMark,
                'deviation_mark' => $deviation
            ];
        }

        return Inertia::render('Reports/Videoconference', [
            'test' => $testResult,
            'vc' => [
                'name' => $vc->name,
                'type' => $vc->settings->type == 'lecture' ? 'Лекция' : 'Практика',
                'count_check' => $metrics->count_check,
                'studgroups' => array_column($vc->studgroups->toArray(), 'name'),
                'date' => $vc->date,
                'user' => $user->full_name
            ],
            'groups' => $groupData,
        ]);

    }


    private function renderError(string $page, string $message)
    {
        return Inertia::render($page, [
            'error' => $message,
        ]);
    }
}
