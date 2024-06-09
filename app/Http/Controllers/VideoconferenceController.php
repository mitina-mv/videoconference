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

        $questions = $this->getQuestions($vc, $user);
        if ($questions instanceof \Illuminate\Http\Response) {
            return $questions; // здесь возвращаем ошибку
        }

        if (!$this->userCanAccessRoom($vc, $user)) {
            return $this->renderError('У вас нет доступа к этой видеоконференции');
        }

        return $this->connectToSession($vc, $user, $questions);
    }

    private function renderError(string $message)
    {
        return Inertia::render('Videoconference/Conference', ['error' => $message]);
    }

    private function getQuestions($vc, $user)
    {
        if ($vc->assignment && $vc->user_id == $user->id) {
            $testSettings = $vc->assignment->test->settings;

            if ($testSettings->question_ids) {
                return Question::whereIn('id', $testSettings->question_ids)
                    ->with(['answers' => function ($query) {
                        $query->select('id', 'question_id', 'name');
                    }])
                    ->select('id', 'text', 'type')
                    ->get()
                    ->each(function($row) {
                        $row->setHidden(['correct_answers']);
                    });
            } else {
                return $this->renderError('Неправильные настройки используемого теста: необходимо использовать тест с предустановленными вопросами.');
            }
        }

        return null;
    }

    private function userCanAccessRoom($vc, $user)
    {
        // завершена ли конференция
        if ($vc->is_completed) {
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
    
            $connection = $this->getConnection($vc, $user, $this->openViduService, $questions);
    
            return Inertia::render('Videoconference/Conference', [
                'sessionId' => $vc->session,
                'token' => $connection['token'],
                'role' => $vc->user_id == $user->id 
                        ? 'MODERATOR' 
                        : ($vc->settings->type == 'practice' ? 'PUBLISHER' : 'SUBSCRIBER'),
                'type' => $vc->settings->type,
                'messages' => $vc->messages,
                'questions' => $questions,
                'backLink' => 'videoconferences.index',
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
                'data' => json_encode(['user_id' => $user->id, 'username' => $user->full_name])
            ]);
        } else {
            $role = 'SUBSCRIBER';
            if ($vc->settings->type == 'practice') {
                $role = 'PUBLISHER';
                $this->createTestLogAndAnswerLogs($vc, $user, $questions);
            }
    
            return $this->openViduService->connectToSession($vc->session, [
                'role' => $role,
                'data' => json_encode([
                    'user_id' => $user->id,
                    'username' => $user->full_name,
                    'sg_name' => $user->sg_name
                ])
            ]);
        }
    }
    private function createTestLogAndAnswerLogs($vc, $user, $questions)
    {
        if ($vc->assignment) {
            $testLog = Testlog::create([
                'mark' => 0,
                'time' => 0,
                'user_id' => $user->id,
                'assignment_id' => $vc->assignment->id,
                'uncorrect_answers' => 0,
            ]);

            foreach ($questions as $question) {
                Answerlog::create([
                    'question_id' => $question->id,
                    'testlog_id' => $testLog->id,
                    'mark' => 0,
                ]);
            }
        }
    }
}
