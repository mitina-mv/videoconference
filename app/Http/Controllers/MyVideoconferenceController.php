<?php

namespace App\Http\Controllers;

use App\Http\Service\OpenViduService;
use App\Http\Service\TestlogService;
use App\Models\Videoconference;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MyVideoconferenceController extends Controller
{
    public function index()
    {
        return Inertia::render('Videoconference/My', [
        ]);
    }
}
