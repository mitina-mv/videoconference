<?php 

namespace App\Http\Service;

use App\Models\Studgroup;
use App\Models\Test;

class TestlogService 
{
    public function getStudgroups()
    {
        $user = auth()->user();

        $studgroups = $user->studgroups->load('students');
        
        return $studgroups;
    }

    public function getTests()
    {
        return Test::where('user_id', auth()->id())->get();
    }
}