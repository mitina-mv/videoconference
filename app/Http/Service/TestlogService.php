<?php

namespace App\Http\Service;

use App\Models\Assignment;
use App\Models\Studgroup;
use App\Models\Test;
use App\Models\Testlog;

class TestlogService
{
    public function getAllStudgroups()
    {
        $user = auth()->user();

        $studgroups = $user->studgroups->load('students');

        return $studgroups;
    }

    public function getStudgroupsWithStudents()
    {
        $user = auth()->user();

        $studgroups = $user->studgroups->load('students');

        $studgroupsWithStudents = $studgroups->filter(function ($studgroup) {
            return $studgroup->students->isNotEmpty();
        });

        return $studgroupsWithStudents;
    }

    public function getTests()
    {
        return Test::where('user_id', auth()->id())->get();
    }

    public function studgroupsAssignment()
    {
        $ids = Assignment::where('user_id', auth()->id())
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();
        
        $studgroups = Testlog::whereIn(
                'assignment_id',
                $ids
            )
            ->with('user.studgroup')
            ->get()
            ->pluck('user.studgroup')
            ->unique();

        return $studgroups;
    }

    public function themesAssignment()
    {
        $themes = Test::where("user_id", auth()->id())
            ->with('theme')
            ->get()
            ->pluck('theme')
            ->unique();

        return $themes;
    }
}
