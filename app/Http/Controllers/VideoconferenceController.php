<?php

namespace App\Http\Controllers;

use App\Http\Service\OpenViduService;
use App\Http\Service\TestlogService;
use App\Models\Videoconference;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VideoconferenceController extends Controller
{
    public function __construct(
        public TestlogService $testlogService
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
            'studgroups' => $user->studgroups,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        return Inertia::render('Videoconference/Form', [
            'tests' => $user->tests,
            'studgroups' => $user->studgroups,            
        ]);
    }

    public function room(string $session)
    {
        $vc = Videoconference::where('session', $session)->first();
        $user = auth()->user();
    
        if(!$vc) {
            return Inertia::render('Videoconference/Conference', [
                'error' => 'Эта видеоконференция не существует' 
            ]);
        }

        // проверка доступа студента к комнате
    
        $openViduService = new OpenViduService();
    
        try {
            // получаем сессию
            if(!$openViduService->sessionExists($vc->session)) {
                if($vc->user_id != $user->id) {
                    return Inertia::render('Videoconference/Conference', [
                        'error' => 'Эта видеоконференция еще не началась' 
                    ]);
                }
                // создаем сессию, если она не существует
                $openViduService->createSession($vc->session);
            }

            if ($vc->user_id == $user->id) {
                // Пользователь владелец конференции, подключаем как модератора
                $connection = $openViduService->connectToSession($vc->session, [
                    'role' => 'MODERATOR',
                    'data' => json_encode(['user_id' => $user->id, 'username' => $user->full_name])
                ]);
            } else {
                // Пользователь не владелец конференции, подключаем как слушателя
                $connection = $openViduService->connectToSession($vc->session, [
                    'role' => 'SUBSCRIBER',
                    'data' => json_encode(['user_id' => $user->id, 'username' => $user->full_name])
                ]);
            }
    
            return Inertia::render('Videoconference/Conference', [
                'sessionId' => $vc->session,
                'token' => $connection['token'],
                'role' => $vc->user_id == $user->id ? 'MODERATOR' : 'SUBSCRIBER',
                'type' => 'lection'
            ]);
    
        } catch (\Exception $e) {
            dump($e); 
            return Inertia::render('Videoconference/Conference', [
                'error' => 'Не удалось подключиться к видеоконференции'
            ]);
        }
    }
}
