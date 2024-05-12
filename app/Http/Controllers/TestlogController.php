<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Studgroup;
use App\Models\Test;
use App\Models\Testlog;
use App\Models\Theme;
use Inertia\Inertia;

class TestlogController extends Controller
{
    public function index()
    {
        $disciplines = Testlog::where('teacher_id', auth()->id())
            ->with('test.theme.discipline')
            ->get()
            ->pluck('test.theme.discipline')
            ->unique();

        return Inertia::render('Testlog/Index', [
            'disciplines' => array_values($disciplines->toArray()),
        ]);
    }

    public function edit(string $id)
    {
        // $studgroups = Studgroup::where();
        
        return Inertia::render('Test/Form', [
            'id' => $id,
            'disciplines' => Discipline::all(),
            'studgroups' => [],
        ]);
    }

    public function create()
    {
        return Inertia::render('Test/Form', [
            'disciplines' => Discipline::all(),
            'studgroups' => [],
            
        ]);
    }
}
