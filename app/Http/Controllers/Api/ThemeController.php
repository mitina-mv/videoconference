<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ThemeRequest;
use App\Models\Theme;
use App\Policies\AdminPolicy;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;

class ThemeController extends Controller
{
    // use DisableAuthorization;
    use DisablePagination;

    protected $model = Theme::class;
    protected $policy = AdminPolicy::class;
    protected $request = ThemeRequest::class;

    public function filterableBy() : array
    {
        return ['discipline_id', ];
    }

    public function aggregates() : array
    {
        return ['questions', ];
    }
}
