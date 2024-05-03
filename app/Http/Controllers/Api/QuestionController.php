<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Policies\TruePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as Request;

class QuestionController extends Controller
{
    // use DisableAuthorization;

    protected $model = Question::class;
    protected $policy = TruePolicy::class;
    protected $request = QuestionRequest::class;

    public function limit() : int
    {
        return 50;
    }

    public function maxLimit() : int
    {
        return 100;
    }

    /**
     * Builds Eloquent query for fetching entities in index method.
     *
     * @param Request $request
     * @param array $requestedRelations
     * @return Builder
     */
    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);

        $query->where('user_id', '=', request()->id());

        return $query;
    }

}
