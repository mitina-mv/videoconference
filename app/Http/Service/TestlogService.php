<?php 

namespace App\Http\Service;

use App\Models\Studgroup;
use App\Models\Test;

class TestlogService 
{
    public function getAllStudgroups()
    {
        $user = auth()->user();

        $studgroups = $user->studgroups->load('students');
        
        return $studgroups;
    }

    public function getStudgroupsWistStudents()
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
}