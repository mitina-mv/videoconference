<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Theme;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index()
    {
        $disciplines = Test::where('user_id', auth()->id())
            ->with('theme.discipline')
            ->get()
            ->pluck('theme.discipline')
            ->unique();

        return Inertia::render('Test/Index', [
            'disciplines' => array_values($disciplines->toArray()),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('Test/Form', [
            'id' => $id,
            'themes' => Theme::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Test/Form', [
            'themes' => Theme::all(),
        ]);
    }
}
