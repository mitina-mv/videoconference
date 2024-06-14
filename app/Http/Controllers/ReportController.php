<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Testlog;
use App\Models\User;
use App\Models\Videoconference;
use Illuminate\Support\Facades\View;
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

            // расчет вовлеченности
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
                'mark' => '-',
                'count_check' => $student_actions['count_check'],
                'testlog_id' => null
            ];
        }

        if ($vc->assignment && $vc->assignment->testlogs) {
            $marks = [];
            $test = $vc->assignment->test;

            foreach ($vc->assignment->testlogs as $testlog) {
                if (!isset($students[$testlog->user_id])) {
                    continue;
                }

                $students[$testlog->user_id]['mark'] = $testlog->mark ? $testlog->mark : 'Нет';
                $countAnswers = $testlog->answerlogs()->where('mark', '!=', null)->count();
                if ($countAnswers > 0) {
                    $p = $test->settings->count_questions / $countAnswers;
                } else {
                    $p = 0;
                }

                if (!empty($testlog->mark)) {
                    $marks[] = $testlog->mark;
                    $p = 0;
                }

                $students[$testlog->user_id]['engagement']['p'] = $p;
                $students[$testlog->user_id]['testlog_id'] = $testlog->id;
            }

            // метрики
            $avgMark = null;
            $deviation = null;
            if (count($marks) > 0) {
                $avgMark = round(array_sum($marks) / count($marks), 2);
                $deviationMarks = array_map(fn ($mark) => pow($mark - $avgMark, 2), $marks);
                $deviation = round(sqrt(array_sum($deviationMarks) / count($marks)), 2);
            }
        }

        foreach ($students as &$student) {
            $engagement = $student['engagement'];
            $ai = $engagement['a'] * env('THRESHOLD_COUNT_CHECK');
            $pi = $engagement['p'] * env('THRESHOLD_COUNT_QUESTION');
            $ci = $engagement['c'] * env('THRESHOLD_COUNT_MESSAGE');
            $hi = $engagement['h'] * env('THRESHOLD_COUNT_HAND');
            $student['engagement'] = round($ai + $pi + $ci + $hi, 2);

            $groupData[$student['group']][] = $student;
        }


        $comments = [];
        $testResult = null;
        if ($vc->assignment) {
            $theme = $vc->assignment->test->theme()->with('discipline')->first();

            $testResult = [
                ['label' => 'Название теста', 'value' => $vc->assignment->test->name],
                ['label' => 'Дисциплина', 'value' => $theme->discipline->name],
                ['label' => 'Тема', 'value' =>  $theme->name],
                ['label' => 'Средняя оценка', 'value' => $avgMark],
                ['label' => 'Станндартное отклонение баллов', 'value' => $deviation],
            ];
        } else {
            $comments[] = "Вы не использовали интерактиные опросы, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за тестирование проставлены на максимум для каждого студента. Интерактивные опросы позволяют сильнее вовлечь аудиторию в процесс обучения, попробуйте!";
        }

        if ($vc->messages == null) {
            $comments[] = "Вы не использовали чат, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за участие в чате проставлены на максимум для каждого студента. Чат - инструмент для моментального взаимодействия с аудиторией. Может быть очень полезным!";
        }

        if ($metrics->count_check == 0) {
            $comments[] = "Вы не использовали проверки присуствия, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за проверки присутствия проставлены на максимум для каждого студента. Проверки присуствия помогают понять, не потеряна ли активная аудитория. Попробуйте в следующий раз!";
        }

        return View::make('reports.vc', [
            'testInfoFields' => $testResult,
            'vc' => [
                'name' => $vc->name,
                'count_check' => $metrics->count_check,
                'flag_mes' => $vc->messages == null
            ],
            'groups' => $groupData,
            'inctuleHeader' => true,
            'includeComments' => true,
            'includeHrefDetail' => false,
            'vcInfoFields' => [
                ['label' => 'Название', 'value' => $vc->name],
                ['label' => 'Дата проведения', 'value' => $vc->date],
                ['label' => 'Тип', 'value' => $vc->settings->type == 'lecture' ? 'Лекция' : 'Практика'],
                ['label' => 'Преподаватель', 'value' => $user->full_name],
                ['label' => 'Количество проверок присуствия', 'value' => $metrics->count_check],
                ['label' => 'Группы', 'value' => implode(', ', array_column($vc->studgroups->toArray(), 'name'))],
            ],
            'comments' => $comments,
            'error' => null
        ])->render();
    }

    public function detail()
    {
        return Inertia::render('Reports/StudentDetail', []);
    }

    private function renderError(string $page, string $message)
    {
        return Inertia::render($page, [
            'error' => $message,
        ]);
    }
}
