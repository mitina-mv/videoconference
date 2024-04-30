<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\Controller;

class UserController extends Controller
{
    // use DisableAuthorization;

    protected $model = User::class;

    public function resolveUser()
    {
        return Auth::guard('sanctum')->user();
    }
}
