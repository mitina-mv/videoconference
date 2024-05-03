<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Auth;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\RelationController;

class UserStudgroupsController extends RelationController
{
    use DisableAuthorization;

    protected $model = User::class;
    // protected $policy = AdminPolicy::class;
    protected $relation = 'studgroups';

    protected $pivotFillable = ['user_id', 'studgroup_id'];
    protected $pivotJson = ['studgroup_id'];
}
