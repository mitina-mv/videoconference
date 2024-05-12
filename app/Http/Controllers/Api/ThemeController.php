<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ThemeRequest;
use App\Models\Theme;
use App\Policies\AdminPolicy;
use Orion\Http\Controllers\Controller;

class ThemeController extends Controller
{
    // use DisableAuthorization;

    protected $model = Theme::class;
    protected $policy = AdminPolicy::class;
    protected $request = ThemeRequest::class;

    // TODO add paginate
    public function limit() : int
    {
        return Theme::all(['id'])->count();
    }

    public function maxLimit() : int
    {
        return Theme::all(['id'])->count();
    }

    public function filterableBy() : array
    {
        return ['discipline_id', ];
    }

    public function aggregates() : array
    {
        return ['questions', ];
    }
}
