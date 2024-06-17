<?php

namespace App\Http\Controllers;

use App\Http\Service\OpenViduService;
use App\Http\Service\TestlogService;
use App\Models\Answerlog;
use App\Models\Question;
use App\Models\Testlog;
use App\Models\Videoconference;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class VideoconferenceController extends Controller
{
    public function __construct(
        public TestlogService $testlogService,
        public OpenViduService $openViduService,
    )
    {  
    }
    
    public function index()
    {
        $user = auth()->user();
        // забираем года и количество назначенных вк
        $arYears = Videoconference::where([
            'user_id' => auth()->id(),
            ])
            ->select(
                DB::raw("to_char(date, 'YYYY') as year"),
                DB::raw("count(id) as count_test"),
            )
            ->orderBy('year', 'desc')
            ->groupBy(
                'year'
            )
            ->get()
            ->toArray();

        return Inertia::render('Videoconference/Index', [
            'years' => $arYears,
            'studgroups' => $user->studgroups,
        ]);
    }

    public function edit($id)
    {
        $user = auth()->user();

        return Inertia::render('Videoconference/Form', [
            'id' => $id,
            'tests' => $user->tests,
            'backLink' => 'videoconferences.index',
            'studgroups' => $user->studgroups,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        return Inertia::render('Videoconference/Form', [
            'tests' => $user->tests,
            'backLink' => 'videoconferences.index',
            'studgroups' => $user->studgroups,            
        ]);
    }

    public function room(string $session)
    {
        $vc = Videoconference::where('session', $session)->first();
        $user = auth()->user();

        if (!$vc) {
            return $this->renderError('Эта видеоконференция не существует');
        }

        if ($vc->is_completed) {
            return $this->renderError('Эта видеоконференция уже закончилась');
        }

        if (!$this->userCanAccessRoom($vc, $user)) {
            return $this->renderError('У вас нет доступа к этой видеоконференции');
        }

        // расчет сложности темы 
        if($vc->user_id == $user->id) {

        }

        $questions = $this->getQuestions($vc, $user);
        if ($questions instanceof InertiaResponse) {
            return $questions; // здесь возвращаем ошибку
        }


        return $this->connectToSession($vc, $user, $questions);
    }

    private function renderError(string $message)
    {
        return Inertia::render(
            'Videoconference/Conference', 
            [
                'error' => $message,
            ]
        );
    }

    private function getQuestions($vc, $user)
    {
        if ($vc->assignment) {
            $testSettings = $vc->assignment->test->settings;

            if($vc->user_id == $user->id && isset($testSettings['question_ids'])) {
                return Question::whereIn('id', $testSettings->question_ids)
                    ->with(['answers' => function ($query) {
                        $query->select('id', 'question_id', 'name');
                    }])
                    ->select('id', 'text', 'type')
                    ->get()
                    ->each(function($row) {
                        $row->setHidden(['correct_answers']);
                    });
            } else if (empty($testSettings['question_ids']) && $vc->user_id == $user->id){
                return $this->renderError('Неправильные настройки используемого теста: необходимо использовать тест с предустановленными вопросами.');
            } else {
                return $testSettings['question_ids'];
            }
        }

        return null;
    }

    private function userCanAccessRoom($vc, $user)
    {        
        // завершена ли конференция
        if ($vc->is_old && !$vc->is_active) {
            return false;
        }

        // активна ли конференция
        if (!$vc->is_active) {
            return false;
        }
        
        // создатель конференции
        if ($vc->user_id == $user->id) {
            return true;
        }

        // принадлежит ли студент к разрешенной группе
        if ($user->studgroup_id) {
            $userGroupIds = [$user->studgroup_id];

            $hasAccess = $vc->studgroups()->whereIn('studgroup_id', $userGroupIds)->exists();
    
            return $hasAccess;
        }

        // по умолчанию всем запрещаем
        return false;
    }

    private function connectToSession($vc, $user, $questions)
    {
        try {
            if (!$this->openViduService->sessionExists($vc->session)) {
                if ($vc->user_id != $user->id) {
                    return $this->renderError('Эта видеоконференция еще не началась');
                }
                $this->openViduService->createSession($vc->session);
            }
    
            $connection = $this->getConnection($vc, $user, $questions);

            $conScreen = null;
            if($vc->user_id == $user->id || $vc->settings->type == 'practice') {
                $conScreen = $this->openViduService->connectToSession($vc->session, [
                    'data' => json_encode(['user_id' => $user->id, 'username' => 'screen'])
                ]);
            }

            if($vc->assignment) {
                $testlog = Testlog::where('user_id', $user->id)
                    ->where('assignment_id', $vc->assignment->id)
                    ->first();
            }

            // dd($connection['token'], $conScreen['token']);
    
            return Inertia::render('Videoconference/Conference', [
                'sessionId' => $vc->session,
                'token' => $connection['token'],
                'tokenScreen' => $conScreen ? $conScreen['token'] : $conScreen,
                'role' => $vc->user_id == $user->id 
                        ? 'MODERATOR' 
                        : ($vc->settings->type == 'practice' ? 'PUBLISHER' : 'SUBSCRIBER'),
                'type' => $vc->settings->type,
                'messages' => $vc->messages,
                'questions' => $questions,
                'backLink' => 'videoconferences.index',
                'settings' => $vc->settings,
                'testlog' => empty($testlog) ? null : $testlog->id
            ]);
    
        } catch (\Exception $e) {
            return $this->renderError('Не удалось подключиться к видеоконференции');
        }
    }

    private function getConnection($vc, $user, $questions)
    {        
        if ($vc->user_id == $user->id) {
            return $this->openViduService->connectToSession($vc->session, [
                'role' => 'MODERATOR',
                'data' => json_encode(['user_id' => $user->id, 'username' => $user->full_name, 'role' => 'MODERATOR'])
            ]);
        } else {
            $this->createTestLogAndAnswerLogs($vc, $user, $questions);

            $role = 'SUBSCRIBER';
            if ($vc->settings->type == 'practice') {
                $role = 'PUBLISHER';
            }
    
            return $this->openViduService->connectToSession($vc->session, [
                'role' => $role,
                'data' => json_encode([
                    'user_id' => $user->id,
                    'username' => $user->full_name,
                    'sg_name' => $user->sg_name,
                    'role' => $role
                ])
            ]);
        }
    }

    private function createTestLogAndAnswerLogs($vc, $user, $questions)
    {
        if ($vc->assignment) {
            $existingTestLog = Testlog::where('user_id', $user->id)
                ->where('assignment_id', $vc->assignment->id)
                ->first();

            if ($existingTestLog) {
                return;
            }

            $testLog = Testlog::create([
                'user_id' => $user->id,
                'assignment_id' => $vc->assignment->id,
            ]);

            foreach ($questions as $id) {
                Answerlog::create([
                    'question_id' => $id,
                    'testlog_id' => $testLog->id,
                ]);
            }
        }
    }

    public function detail(string $vc_id)
    {
        try {
            $dataVC = (new ReportController())->videoconferenceData($vc_id);
        } catch (\Exception $e) {
            return Inertia::render('Videoconference/Detail', [
                'error' => $e->getMessage(),
            ]);
        }
        return Inertia::render('Videoconference/Detail', [
            ...$dataVC,
            'backLink' => 'videoconferences.index',
        ]);
    }
}
