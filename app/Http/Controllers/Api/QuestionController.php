<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Policies\TeacherPolicy;
use App\Policies\TruePolicy;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as Request;

class QuestionController extends Controller
{
    // use DisableAuthorization;

    protected $model = Question::class;
    protected $policy = TeacherPolicy::class;
    protected $request = QuestionRequest::class;

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
        return ['answers', 'theme'];
    }

    public function filterableBy() : array
    {
        return ['theme.discipline_id', 'theme_id', 'user_id', 'is_private'];
    }
}
