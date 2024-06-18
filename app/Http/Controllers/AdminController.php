<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Index', []);
    }

    public function edit(string $id)
    {
        $role = User::find($id)->role_id;
        return Inertia::render('Admin/EditUser', [
            'id' => $id,
            'labelgroup' => $role == Role::ROLE_TEACHER ? 'teachers' : 'students',
            'role' => $role,
        ]);
    }

    public function create(string $role)
    {
        return Inertia::render('Admin/EditUser', [
            'role' => $role == 'teachers' ? Role::ROLE_TEACHER : Role::ROLE_STUDENT,
            'labelgroup' => $role,
        ]);
    }

    public function studgroups()
    {
        return Inertia::render('Admin/Reference', [
            'entity' => 'studgroups'
        ]);
    }

    public function disciplines()
    {
        return Inertia::render('Admin/Reference', [
            'entity' => 'disciplines'
        ]);
    }

    public function themes()
    {
        return Inertia::render('Admin/Reference', [
            'entity' => 'themes',
            'addColumns' => [
                [
                    'entity' => 'disciplines',
                    'type' => 'dropdown',
                    'code' => 'discipline_id',
                    'title' => 'Дисциплина',
                    'options' => Discipline::all(),
                ]
            ]
        ]);
    }
}
