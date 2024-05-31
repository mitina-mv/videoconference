<?php

namespace App\Http\Controllers;

use App\Http\Service\TestlogService;
use App\Models\Videoconference;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VideoconferenceController extends Controller
{
    public function __construct(
        public TestlogService $testlogService
    )
    {  
    }
    
    public function index()
    {
        // забираем года и количество назначенных вк
        $arYears = Videoconference::where([
            'user_id' => auth()->id(),
            ])
            ->select(
                DB::raw("to_char(date, 'YYYY') as year"),
                DB::raw("count(id) as count_test"),
            )
            ->orderBy('year', 'desc')
            ->groupBy(
                'year'
            )
            ->get()
            ->toArray();

        return Inertia::render('Videoconference/Index', [
            'years' => $arYears,
        ]);
    }

}
