<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $vc_id = $request->input('vc_id');
        $files = $request->file('files');
        $uploadedFiles = [];

        $existingFiles = File::where('videoconference_id', $vc_id)->get()->toArray();

        dump($existingFiles);
        
        if (count($existingFiles) + count($files) > 10) {
            return response()->json(['message' => 'Максимальное количество файлов - 10'], 400);
        }

        foreach ($files as $file) {
            $path = $file->store('uploads', 'public');
            $fileRecord = File::create([
                'videoconference_id' => $vc_id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
            ]);
            $uploadedFiles[] = $fileRecord;
        }

        return response()->json(['files' => $uploadedFiles]);
    }

    public function delete(Request $request)
    {
        $filePath = $request->input('file.path');
        $vc_id = $request->input('vc_id');

        $file = File::where('path', $filePath)->where('videoconference_id', $vc_id)->first();

        if ($file) {
            Storage::disk('public')->delete($filePath);
            $file->delete();
            return response()->json(['message' => 'Файл успешно удален']);
        }

        return response()->json(['message' => 'Файл не найден'], 404);
    }
}
