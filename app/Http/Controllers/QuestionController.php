<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function index()
    {
        return Inertia::render('Question/Index', []);
    }

    public function edit(string $id)
    {
        return Inertia::render('Question/Form', [
            'id' => $id,
            'themes' => Theme::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Question/Form', [
            'themes' => Theme::all(),
        ]);
    }
}
