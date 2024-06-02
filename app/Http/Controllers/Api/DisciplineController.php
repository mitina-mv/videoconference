<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DisciplineRequest;
use App\Models\Discipline;
use App\Policies\AdminPolicy;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;

class DisciplineController extends Controller
{
    // use DisableAuthorization;
    use DisablePagination;

    protected $model = Discipline::class;
    protected $policy = AdminPolicy::class;
    protected $request = DisciplineRequest::class;

    public function filterableBy() : array
    {
        return ['id', ];
    }

    public function sortableBy() : array
    {
         return ['id', 'name', ];
    }
}
