<?php

namespace App\Http\Controllers\Api;

use App\Models\Assignment;
use App\Models\Role;
use App\Models\Test;
use App\Models\Testlog;
use App\Policies\TruePolicy;
use Carbon\Carbon;
use Orion\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Requests\Request as Request;

class AssignmentController extends Controller
{
    // use DisableAuthorization;

    protected $model = Assignment::class;
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
        return ['test', 'test.theme', 'testlogs', 'testlogs.user', 'testlogs.user.studgroup',];
    }

    public function filterableBy(): array
    {
        return ['test.theme_id',];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        $query->where('user_id', '=', request()->user()->id);
        return $query;
    }

    public function beforeUpdate(Request $request, $assignment)
    {
        if ($request->has('date')) {
            $newDate = new Carbon($request->input('date'));
            $nowDate = new Carbon();

            if ($newDate < $nowDate) {
                abort(422, 'Дата мероприятия не может быть позже текущей даты');
            }

            $curDate = new Carbon($assignment->date);

            // Проверяем, была ли существующая дата мероприятия изменена на прошлую
            if ($curDate < $nowDate) {
                abort(422, 'Нельзя редактировать уже прошедшее назначение.');
            }
        }
    }

    public function studgroups(Request $request) 
    {
        $studgroups = Testlog::whereIn('assignment_id', $request->ids)
        ->with('user.studgroup')
        ->get()
        ->pluck('user.studgroup')
        ->unique();

        return $studgroups;
    }

    // public function getDisciplines() 
    // {
    //     dd(auth()->id());
        // $disciplines = Test::where("user_id", auth()->id())
        //     ->with('theme.discipline')
        //     ->get()
        //     ->pluck('theme.discipline')
        //     ->unique();

        // dd($disciplines);
        
        // return $disciplines;
    // }

    public function themes(Request $request)
    {
        $themes = Test::where("user_id", auth()->id())
            ->with('theme')
            ->get()
            ->pluck('theme')
            ->unique();

        return $themes;
    }
}
