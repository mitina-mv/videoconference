<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Auth;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\Controller;

class UserController extends Controller
{
    // use DisableAuthorization;

    protected $model = User::class;
    protected $policy = AdminPolicy::class;
    protected $request = UserRequest::class;

    public function limit() : int
    {
        return 25;
    }

    public function maxLimit() : int
    {
        return 100;
    }

    public function filterableBy() : array
    {
        return ['role_id', 'studgroup_id', 'is_verify',];
    }

    public function searchableBy() : array
    {
        return ['name', 'lastname', 'email', ];
    }

    public function sortableBy() : array
    {
         return ['id', 'name', 'lastname', ];
    }

    public function includes() : array
    {
        return ['studgroups'];
    }
}
