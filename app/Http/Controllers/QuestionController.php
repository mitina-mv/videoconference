<?php

namespace App\Http\Controllers;

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
        ]);
    }

    public function create(string $role)
    {
        return Inertia::render('Question/Form', [
        ]);
    }
}
