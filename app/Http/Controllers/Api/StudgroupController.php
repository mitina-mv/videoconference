<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StudgroupRequest;
use App\Models\Studgroup;
use App\Policies\AdminPolicy;
use App\Policies\NoStudentPolicy;
use Illuminate\Support\Facades\Auth;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;

class StudgroupController extends Controller
{
    // use DisableAuthorization;
    use DisablePagination;

    protected $model = Studgroup::class;
    protected $policy = NoStudentPolicy::class;
    protected $request = StudgroupRequest::class;
}
