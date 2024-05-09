<?php

namespace App\Http\Controllers\Api;

use App\Models\Answer;
use App\Policies\TruePolicy;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as Request;

class AnswerController extends Controller
{
    // use DisableAuthorization;

    protected $model = Answer::class;
    protected $policy = TruePolicy::class;
    // protected $request = QuestionRequest::class;

    public function limit() : int
    {
        return 25;
    }

    public function maxLimit() : int
    {
        return 100;
    }
}
