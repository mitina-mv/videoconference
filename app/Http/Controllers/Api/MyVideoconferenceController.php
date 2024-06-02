<?php

namespace App\Http\Controllers\Api;

use App\Models\Videoconference;
use App\Policies\TruePolicy;
use Carbon\Carbon;
use Orion\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Orion\Http\Requests\Request as Request;

class MyVideoconferenceController extends Controller
{
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
        return ['user', 'assignment.test', 'studgroups',];
    }

    public function filterableBy(): array
    {
        return ['date',];
    }

    public function sortableBy() : array
    {
        return ['date', 'name'];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);

        $user = Auth::user();

        $userGroupIds = [$user->studgroup_id];

        $query->whereHas('studgroups', function (Builder $query) use ($userGroupIds) {
            $query->whereIn('studgroup_id', $userGroupIds);
        });
        
        return $query;
    }
}
