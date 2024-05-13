<?php 

namespace App\Http\Service;

use App\Models\Studgroup;
use App\Models\Test;

class TestlogService 
{
    public function getStudgroups()
    {
        $user = auth();

        $studgroups = Studgroup::whereHas('users_studgroups', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('students')->get();

        return $studgroups;
    }

    public function getTests()
    {
        return Test::where('user_id', auth()->id())->get();
    }
}