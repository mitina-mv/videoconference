<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Policies\TeacherPolicy;
use App\Policies\TruePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as Request;

class QuestionController extends Controller
{
    // use DisableAuthorization;

    protected $model = Question::class;
    protected $policy = TeacherPolicy::class;
    protected $request = QuestionRequest::class;

    public function limit() : int
    {
        return 25;
    }

    public function maxLimit() : int
    {
        return 100;
    }

    public function includes() : array
    {
        return ['answers', 'theme'];
    }

    public function filterableBy() : array
    {
        return ['theme.discipline_id', 'theme_id', 'user_id', 'is_private'];
    }

    public function upload(Request $request)
    {
        $question_id = $request->input('question_id');
        $files = $request->file('files');

        dump($question_id, $request);

        $question = Question::where('id', $question_id )->first();
        
        dump($question);

        if (!$question) {
            return response()->json(['message' => 'не найден вопрос'], 404);
        }

        if ($question->path) {
            Storage::disk('public')->delete($question->path);
        }

        $question->update([
            'path' => $files[0]->store('uploads', 'public'),
        ]);

        return response()->json(['file' => $question->path_full]);
    }
    public function deleteImage(Request $request)
    {
        $question_id = $request->input('question_id');
        $question = Question::where('id', $question_id)->first();

        if (!$question) {
            return response()->json(['message' => 'не найден вопрос'], 404);
        }

        if ($question->path) {
            Storage::disk('public')->delete($question->path);
        }

        return response()->json(['message' => 'Файл успешно удален']);
    }
}
