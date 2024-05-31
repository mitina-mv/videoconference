<?php

namespace App\Http\Controllers\Api;

use App\Models\Videoconference;
use App\Policies\TruePolicy;
use Orion\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Requests\Request as Request;

class VideoconferenceController extends Controller
{
    // use DisableAuthorization;

    protected $model = Videoconference::class;
    protected $policy = TruePolicy::class;

    public function limit(): int
    {
        return 25;
    }

    public function maxLimit(): int
    {
        return 100;
    }

    public function includes(): array
    {
        return ['assignment', 'assignment.test', 'assignment.test.theme', 'studgroups',];
    }

    public function filterableBy(): array
    {
        return ['date'];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        $query->where('user_id', '=', request()->user()->id);

        if ($request->has('year')) {
            $year = $request->input('year');
            $query->whereYear('date', $year);
        }
        
        return $query;
    }
}
