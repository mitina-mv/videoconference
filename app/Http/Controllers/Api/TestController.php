<?php

namespace App\Http\Controllers\Api;

use App\Models\Test;
use App\Policies\TruePolicy;
use Orion\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Requests\Request as Request;

class TestController extends Controller
{
    // use DisableAuthorization;

    protected $model = Test::class;
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
        return ['theme', ];
    }

    public function filterableBy() : array
    {
        return ['theme.discipline_id', ];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        $query->where('user_id', '=', request()->user()->id);
        return $query;
    }
}
