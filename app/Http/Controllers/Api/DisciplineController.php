<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DisciplineRequest;
use App\Models\Discipline;
use App\Policies\AdminPolicy;
use Orion\Http\Controllers\Controller;

class DisciplineController extends Controller
{
    // use DisableAuthorization;

    protected $model = Discipline::class;
    protected $policy = AdminPolicy::class;
    protected $request = DisciplineRequest::class;

    public function limit() : int
    {
        return Discipline::all(['id'])->count();
    }

    public function maxLimit() : int
    {
        return Discipline::all(['id'])->count();
    }

    public function filterableBy() : array
    {
        return ['id', ];
    }

    public function sortableBy() : array
    {
         return ['id', 'name', ];
    }
}
