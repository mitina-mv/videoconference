<?php

namespace App\Http\Controllers\Api;

use App\Models\Studgroup;
use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Auth;
use Orion\Http\Controllers\Controller;

class StudgroupController extends Controller
{
    // use DisableAuthorization;

    protected $model = Studgroup::class;
    protected $policy = AdminPolicy::class;

}
