<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StudgroupRequest;
use App\Models\Studgroup;
use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Auth;
use Orion\Http\Controllers\Controller;

class StudgroupController extends Controller
{
    // use DisableAuthorization;

    protected $model = Studgroup::class;
    protected $policy = AdminPolicy::class;
    protected $request = StudgroupRequest::class;

    public function limit() : int
    {
        return 50;
    }

    public function maxLimit() : int
    {
        return 100;
    }

}
