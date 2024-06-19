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
            'user' => $user,
            'questions' => $questions,
            'backLink' => 'assignments.my'
        ]);
    }

    public function assignment(string $assignment_id)
    {
        $assignment = Assignment::where('id', $assignment_id)
            ->with(['testlogs.user.studgroup', 'test'])
            ->withTrashed()
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
                'mark' => $testlog->mark == null ? 'Нет' : $testlog->mark,
                'testlog_id' => $testlog->id
            ];

            if (!empty($testlog->mark)) {
                $marks[] = $testlog->mark;
            }
        }

        if (count($marks) == 0) {
            return $this->renderError('Reports/Assignment', 'Студенты еще не прошли это тестирование.');
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

    public function videoconferenceData(string $vc_id)
    {
        $vc = Videoconference::where('id', $vc_id)
            ->with(['studgroups', 'assignment.test', 'assignment.testlogs'])
            ->first();
        $user = request()->user();

        if (!$vc) {
            throw new \Exception('Назначение не найдено');
        }

        if($vc->user_id != $user->id) {
            throw new \Exception('Вы не можете просматривать этот отчет');
        }

        if(!$vc->is_completed) {
            throw new \Exception('Эта видеоконференция не состоялась');
        }

        $metrics = $vc->metrics;

        // подготовка данных о студентах
        if (empty($metrics->students)) {
            throw new \Exception('В этой конференции не участвовал ни один студент, нет данных для отчета.');
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
            $c = $vc->messages == null ? 1 : $student_actions['count_message'] / env('COUNT_MESSAGES_PERFECT', 20);
            $h = $student_actions['count_hand'] / env('COUNT_HAND_PERFECT', 5);

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
                'count_check' => $student_actions['count_check']
            ];
        }

        if ($vc->assignment && $vc->assignment->testlogs) {
            $marks = [];
            $test = $vc->assignment->test;

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
                $students[$testlog->user_id]['testlog_id'] = $testlog->id;
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

        return [
            'test' => $testResult,
            'vc' => [
                'name' => $vc->name,
                'type' => $vc->settings->type == 'lecture' ? 'Лекция' : 'Практика',
                'count_check' => $metrics->count_check,
                'studgroups' => array_column($vc->studgroups->toArray(), 'name'),
                'date' => $vc->date,
                'user' => $user->full_name,
                'flag_mes' => $vc->messages == null,
                'messages' => $vc->messages,
                'files' => $vc->files,
                'path_full' => $vc->path_full,
            ],
            'groups' => $groupData,
        ];
    }

    public function videoconference(string $vc_id)
    {
        try {
            $data = $this->videoconferenceData($vc_id);
        } catch (\Exception $e) {
            return View::make('reports.vc', [
                'testInfoFields' => null,
                'vc' => null,
                'groups' => null,
                'inctuleHeader' => null,
                'includeComments' => null,
                'includeHrefDetail' => null,
                'vcInfoFields' => null,
                'comments' => null,
                'error' => $e->getMessage()
            ])->render();
        }

        $comments = [];
        $testResult = null;
        if ($data['test']) {
            $testResult = [
                ['label' => 'Название теста', 'value' => $data['test']['name']],
                ['label' => 'Дисциплина', 'value' => $data['test']['discipline']],
                ['label' => 'Тема', 'value' =>  $data['test']['theme']],
                ['label' => 'Средняя оценка', 'value' => $data['test']['avg_mark']],
                ['label' => 'Станндартное отклонение баллов', 'value' => $data['test']['deviation_mark']],
            ];
        } else {
            $comments[] = "Вы не использовали интерактиные опросы, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за тестирование проставлены на максимум для каждого студента. Интерактивные опросы позволяют сильнее вовлечь аудиторию в процесс обучения, попробуйте!";
        }

        if ($data['vc']['flag_mes']) {
            $comments[] = "Вы не использовали чат, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за участие в чате проставлены на максимум для каждого студента. Чат - инструмент для моментального взаимодействия с аудиторией. Может быть очень полезным!";
        }

        if ($data['vc']['count_check'] == 0) {
            $comments[] = "Вы не использовали проверки присуствия, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за проверки присутствия проставлены на максимум для каждого студента. Проверки присуствия помогают понять, не потеряна ли активная аудитория. Попробуйте в следующий раз!";
        }

        return View::make('reports.vc', [
            'testInfoFields' => $testResult,
            'vc' => [
                'name' => $data['vc']['name'],
                'count_check' => $data['vc']['count_check'],
                'flag_mes' => $data['vc']['flag_mes']
            ],
            'groups' => $data['groups'],
            'inctuleHeader' => true,
            'includeComments' => true,
            'includeHrefDetail' => false,
            'vcInfoFields' => [
                ['label' => 'Название', 'value' => $data['vc']['name']],
                ['label' => 'Дата проведения', 'value' => $data['vc']['date']],
                ['label' => 'Тип', 'value' => $data['vc']['type']],
                ['label' => 'Преподаватель', 'value' => $data['vc']['user']],
                ['label' => 'Количество проверок присуствия', 'value' => $data['vc']['count_check']],
                ['label' => 'Группы', 'value' => implode(', ', array_column($data['groups'], 'name'))],
            ],
            'comments' => $comments,
            'error' => null
        ])->render();
    }

    public function detail(string $testlog_id)
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
            return $this->renderError('Reports/Teacher', 'Не найдено тестирование');
        }

        if (!$testlog->mark) {
            return $this->renderError('Reports/Teacher', 'Студент не проходит этот тест');
        }

        if ($testlog->assignment->user_id != $user->id) {
            return $this->renderError('Reports/Teacher', 'Вы не можете просматривать этот отчет');
        }

        $questions = [];
        foreach ($testlog->answerlogs->toArray() as $q) {
            $item = [
                'mark' => $q['mark'],
                'total_mark' => $q['question']['mark'],
                'question' => $q['question']['text'],
                'answers' => empty($q['answers']) ? null : array_column($q['answers'], 'history'),
                'correct' => array_column($q['question']['correct_answers'], 'name'),
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

        return Inertia::render('Reports/Teacher', [
            'test' => $testlog->assignment->test()->select('name')->first(),
            'testlog' => $testlog,
            'user' => $testlog->user()->first(),
            'questions' => $questions,
            'backLink' => back()->getTargetUrl()
        ]);
    }

    public function detailStudent(string $vc_id)
    {
        $vc = Videoconference::where('id', $vc_id)
            ->with([
                'studgroups',
                'files', 
                'assignment.test',
                'user'
            ])
            ->first();
            
        if(!$vc){
            return $this->renderError('Videoconference/DetailStudent', 'Видеоконференция не найдена');
        }
        $user = request()->user();

        $userGroupIds = [$user->studgroup_id];
        $hasAccess = false;
        if($userGroupIds) {
            $hasAccess = $vc->studgroups()->whereIn('studgroup_id', $userGroupIds)->exists();
        }
        if(!$hasAccess){
            return $this->renderError('Videoconference/DetailStudent', 'Вы не можете просматривать этот отчет');
        }

        $testResult = null;
        if($vc->assignment) {
            $theme = $vc->assignment->test->theme()->with('discipline')->first();
            $testlog = Testlog::where([
                'user_id' => $user->id,
                'assignment_id' => $vc->assignment->id,
            ])
            ->first();

            $testResult = [
                'name' => $vc->assignment->test->name,
                'discipline' => $theme->discipline->name,
                'theme' => $theme->name,
                'testlog_id' => $testlog->id,
                'mark' => $testlog->mark
            ];
        }

        return Inertia::render('Videoconference/DetailStudent', [
            'test' => $testResult,
            'vc' => [
                'name' => $vc->name,
                'type' => $vc->settings->type == 'lecture' ? 'Лекция' : 'Практика',
                'count_check' => $vc->metrics->count_check,
                'studgroups' => array_column($vc->studgroups->toArray(), 'name'),
                'date' => $vc->date,
                'user' => $vc->user->full_name,
                'messages' => $vc->messages,
                'files' => $vc->files,
            ],
        ]);
    }

    private function renderError(string $page, string $message)
    {
        return Inertia::render($page, [
            'error' => $message,
            'backLink' => back()->getTargetUrl()
        ]);
    }
}
