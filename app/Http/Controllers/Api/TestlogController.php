<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Test;
use App\Models\Testlog;
use App\Policies\TruePolicy;
use Orion\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Requests\Request as Request;

class TestlogController extends Controller
{
    // use DisableAuthorization;

    protected $model = Testlog::class;
    protected $policy = TruePolicy::class;

    public function limit() : int
    {
        return 25;
    }

    public function maxLimit() : int
    {
        return 100;
    }

    public function includes() : array
    {
        return ['test', ];
    }

    public function filterableBy() : array
    {
        return ['test.theme.discipline_id', ];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        $roleId = request()->user()->role_id;
        if ($roleId == Role::ROLE_TEACHER) {
            $query->where('teacher_id', '=', request()->user()->id);
        } else if ($roleId == Role::ROLE_STUDENT) {
            $query->where('user_id', '=', request()->user()->id);
        }
        return $query;
    }
}
