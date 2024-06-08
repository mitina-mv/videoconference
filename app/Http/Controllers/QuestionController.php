<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Question;
use App\Models\Theme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function index()
    {
        $disciplines = Question::where('user_id', auth()->id())
            ->with('theme.discipline')
            ->get()
            ->pluck('theme.discipline')
            ->unique();

        return Inertia::render('Question/Index', [
            'disciplines' => array_values($disciplines->toArray()),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('Question/Form', [
            'id' => $id,
            'themes' => Theme::all(),
            'backLink' => 'questions.index',
            'disciplines' => Discipline::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Question/Form', [
            'themes' => Theme::all(),
            'backLink' => 'questions.index',
            'disciplines' => Discipline::all(),
        ]);
    }
}
