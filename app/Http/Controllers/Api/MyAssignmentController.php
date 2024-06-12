<?php

namespace App\Http\Controllers\Api;

use App\Models\Testlog;
use App\Policies\TruePolicy;
use App\Http\Service\TestService;
use Carbon\Carbon;
use Orion\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Orion\Http\Requests\Request as Request;

class MyAssignmentController extends Controller
{
    protected $model = Testlog::class;
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
        return ['assignment.test.theme', 'assignment.user','assignment'];
    }

    public function filterableBy(): array
    {
        return [];
    }

    public function sortableBy() : array
    {
        return [];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        $query->where('user_id', '=', request()->user()->id);

        if ($request->has('date')) {
            $date = Carbon::parse($request->date)
                ->timezone('Europe/Moscow')
                ->format('d.m.Y');

            $query->whereHas('assignment', function (Builder $query) use ($date) {
                $query->whereDate('date', $date);
            });
        }

        if ($request->has('discipline')) {
            $discipline = $request->discipline;
            
            if (is_numeric($discipline)) {
                $query->whereHas('assignment.test.theme', function (Builder $query) use ($discipline) {
                    $query->where('discipline_id', $discipline);
                });
            }
        }

        return $query;
    }

    public function saveAnswer(Request $request)
    {
        try {
            $this->validate($request, [
                'testlog_id' => 'required|integer',
                'answers' => 'required|array',
            ]);

            $testService = new TestService();

            $testService->saveAnswers($request->testlog_id, $request->answers);

            return response()->json(['message' => 'Ответы успешно сохранены']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
