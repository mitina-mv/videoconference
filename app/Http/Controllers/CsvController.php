<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class CsvController extends Controller
{
    public function get_csv(string $assignment_id)
    {
        $assignment = Assignment::where('id', $assignment_id)->first();
        if (!$assignment) {
            return response('нет назначения', 404);
        }
        if (!$assignment->moodle_code) {
            return response('нет кода для moodle', 401);
        }

        $testlogs = $assignment->testlogs()->with('user')->get();

        $directory = 'public/cvs';
        $fileName = uniqid() . '.csv';
        $filePath = $directory . '/' . $fileName;

        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        $handle = fopen(storage_path('app/' . $filePath), 'w');

        fputcsv($handle, [
            "email",
            $assignment->moodle_code,
        ]);

        foreach ($testlogs as $testlog) {
            fputcsv($handle, [
                $testlog->user->email,
                $testlog->mark ?? 0,
            ]);
        }

        fclose($handle);

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        return response()->download(storage_path('app/' . $filePath), "{$assignment->moodle_code}_marks.csv", $headers);
    }
}
