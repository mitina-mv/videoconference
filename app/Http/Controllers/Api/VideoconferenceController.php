<?php

namespace App\Http\Controllers\Api;

use App\Models\Answer;
use App\Models\Answerlog;
use App\Models\Testlog;
use App\Models\Videoconference;
use App\Policies\TruePolicy;
use Carbon\Carbon;
use Exception;
use Orion\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
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
        return ['date', 'assignment.test_id', 'studgroups.id'];
    }

    public function sortableBy() : array
    {
        return ['date', 'name'];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        // Удаление фильтра по дате из запроса
        if ($request->has('filters') && !empty($request->input('filters'))) {
            $filtersOld = $request->input('filters');
            $fitlerFields = array_column($filtersOld, 'field');

            $filters = array_filter($filtersOld, function ($filter) {
                return $filter['field'] !== 'date';
            });

            $request->merge(['filters' => array_values($filters)]);
        }

        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        $query->where('user_id', '=', request()->user()->id);

        if (!empty($fitlerFields) 
            && ($key = array_search('date', $fitlerFields)) !== false
        ) {
            $date = Carbon::parse($filtersOld[$key]['value'])
                ->timezone('Europe/Moscow')
                ->format('d.m.Y');
            $query->whereDate('date', $date);
        } else if ($request->has('year')) {
            $year = $request->input('year');
            $query->whereYear('date', $year);
        }

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

    public function sendMessage(Request $request)
    {
        try {
            $vc = Videoconference::where('session', $request->session)
            ->first();
        
            $messages = $vc->messages;
            $messages[] = $request->message;
            $vc->update(['messages' => $messages]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ],
            422);
        }
    }

    public function saveAnswer(Request $request)
    {
        try {
            $testlog = Testlog::where('id', $request->testlog_id)
            ->first();

            if (!$testlog) {
                throw new Exception('Не найдено тестирование');
            }

            $answerlog = Answerlog::where('testlog_id', $testlog->id)
                ->where('question_id', $request->question_id)
                ->first();

            if (!$answerlog) {
                throw new Exception('Не найдено задание');
            }

            // записываем ответы
            if(is_array($request->answer)) {
                $answerlog->answers()->detach();

                foreach ($request->answer as $answerId) {
                    $answerlog->answers()->attach($answerId);
                }
            } else {
                $answer = Answer::where('question_id', $request->question_id)
                    ->where('name', $request->answer)
                    ->first();

                if ($answer) {
                    $answerlog->answers()->detach();
                    $answerlog->answers()->attach($answer->id);
                } else {
                    $uncorrect = $testlog->uncorrect_answers;
                    $uncorrect[$request->question_id] = $request->answer;
                    $testlog->update(['uncorrect_answers' => $uncorrect]);
                }
            }
        
            
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ],
            422);
        }
    }

    public function addCheckControl(Request $request)
    {
        try {
            $vc = Videoconference::where('session', $request->session)
            ->first();
        
            $metrics = $vc->metrics;
            $metrics['count_check'] += 1;
            $vc->update(['metrics' => $metrics]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ],
            422);
        }
    }

    public function addStudentAction(Request $request)
    {
        try {
            $vc = Videoconference::where('session', $request->session)
            ->first();
        
            $metrics = $vc->metrics;

            if (!$metrics['students'][$request->user_id]) {
                $metrics['students'][$request->user_id] = [
                    'count_check' => 0,
                    'count_message' => 0,
                    'count_hand' => 0,
                ];
            }

            $metrics['students'][$request->user_id]["count_{$request->action}"] += 1;
            $vc->update(['metrics' => $metrics]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ],
            422);
        }
    }
}
