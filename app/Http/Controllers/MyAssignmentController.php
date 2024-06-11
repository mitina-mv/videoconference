<?php

namespace App\Http\Controllers;

use App\Models\Testlog;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Request;

class MyAssignmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $disciplines = Testlog::where('user_id', $user->id)
            ->with('assignment.test.theme.discipline')
            ->get()
            ->pluck('assignment.test.theme.discipline')
            ->unique()
            ->toArray();

        array_unshift($disciplines, [
            'name' => 'Все дисциплины',
            'id' => '__all'
        ]);

        return Inertia::render('Assignment/My', [
            'disciplines' => $disciplines
        ]);
    }

    public function testing(Request $request)
    {
        $user = Auth::user();
        dd($user);
    }
}
