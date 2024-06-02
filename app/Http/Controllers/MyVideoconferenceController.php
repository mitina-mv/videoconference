<?php

namespace App\Http\Controllers;

use App\Http\Service\OpenViduService;
use App\Http\Service\TestlogService;
use App\Models\Videoconference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MyVideoconferenceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userGroupIds = [$user->studgroup_id];

        $disciplines = Videoconference::whereHas(
                'studgroups',
                    function (Builder $query) use ($userGroupIds) {
                    $query->whereIn('studgroup_id', $userGroupIds);
                })
            ->with('assignment.test.theme.discipline')
            ->get()
            ->pluck('assignment.test.theme.discipline')
            ->unique()
            ->toArray();

        $key = array_search(null, $disciplines);
        if($key !== false) {
            $disciplines[$key] = [
                'name' => 'Без дисциплины',
                'id' => '__null'
            ];
        } else {
            array_unshift($disciplines, [
                'name' => 'Без дисциплины',
                'id' => '__null'
            ]);
        }

        array_unshift($disciplines, [
            'name' => 'Все конференции',
            'id' => '__all'
        ]);

        return Inertia::render('Videoconference/My', [
            'disciplines' => $disciplines
        ]);
    }
}
