<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AssignmentController as ApiAssignmentController;
use App\Http\Service\TestlogService;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function __construct(
        public TestlogService $testlogService
    )
    {  
    }
    
    public function index()
    {
        // забираем года и количество назначенных тестов
        $yearsTestlog = Assignment::where([
            'user_id' => auth()->id(),
            'vc_id' => null,
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

        return Inertia::render('Assignment/Index', [
            'years' => $yearsTestlog,
            'studgroups' => $this->testlogService->studgroupsAssignment(),
            'tests' => $this->testlogService->getTests(),
            'themes' => $this->testlogService->themesAssignment(),
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Assignment/Form', [
            'id' => $id,
            'tests' => $this->testlogService->getTests(),
            'studgroups' => $this->testlogService->getStudgroupsWithStudents(),
            'backLink' => 'assignments.index',
        ]);
    }

    public function create()
    {
        return Inertia::render('Assignment/Form', [
            'tests' => $this->testlogService->getTests(),
            'backLink' => 'assignments.index',
            'studgroups' => $this->testlogService->getStudgroupsWithStudents(),            
        ]);
    }
}
