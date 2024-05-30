<?php

namespace App\Http\Controllers;

use App\Http\Service\TestlogService;
use App\Models\Discipline;
use App\Models\Studgroup;
use App\Models\Testlog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Request;

class TestlogController extends Controller
{
    public function __construct(
        public TestlogService $testlogService
    )
    {
        
    }
    
    public function index()
    {
        // забираем года и количество назначенных тестов
        $yearsTestlog = Testlog::where([
            'teacher_id' => auth()->id(),
            ])
            ->select(
                DB::raw("to_char(testlog_date, 'YYYY') as year"),
                DB::raw("count(id) as count_test"),
            )
            ->orderBy('year')
            ->groupBy(
                'year'
            )
            ->get()
            ->toArray();

        return Inertia::render('Testlog/Index', [
            'years' => $yearsTestlog,
        ]);
    }

    public function edit($test_id, $date)
    {
        $testLog = Testlog::where([
            'teacher_id' => auth()->id(),
            'test_id' => $test_id,
            'date' => $date
        ])
        ->select(
            'users.full_name',
            'studgroups.name',
            'users.studgroup_id',
            'testlogs.id as testlog_id'
        ) 
        ->join('users', 'users.id', '=', 'testlogs.user_id')
        ->join('studgroups', 'users.studgroup_id', '=', 'studgroups.id')
        ->get();
        
        $testUserInfo = [];
        foreach($testLog->groupBy('studgroup_id') as $groupId => $groupUsers)
        {
            $groupInfo = $groupUsers->first();
            $testUserInfo[$groupId] = [
                'name' => $groupInfo->studgroup_name,
                'users' => $groupUsers->toArray(),
            ];
        }
        
        return Inertia::render('Test/Form', [
            'data' => $testLog,
            'tests' => $this->testlogService->getTests(),
            'studgroups' => $this->testlogService->getStudgroups(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Test/Form', [
            'tests' => $this->testlogService->getTests(),
            'studgroups' => $this->testlogService->getStudgroups(),            
        ]);
    }
}
