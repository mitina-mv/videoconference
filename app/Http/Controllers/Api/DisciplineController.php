<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DisciplineRequest;
use App\Http\Requests\StudgroupRequest;
use App\Models\Discipline;
use App\Models\Studgroup;
use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Auth;
use Orion\Http\Controllers\Controller;

class DisciplineController extends Controller
{
    // use DisableAuthorization;

    protected $model = Discipline::class;
    protected $policy = AdminPolicy::class;
    protected $request = DisciplineRequest::class;

    public function limit() : int
    {
        return 50;
    }

    public function maxLimit() : int
    {
        return 100;
    }

}
