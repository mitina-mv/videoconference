<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AssignmentTestlogController;
use App\Http\Controllers\Api\DisciplineController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\MyAssignmentController;
use App\Http\Controllers\Api\MyVideoconferenceController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\StudgroupController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\TestlogController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserStudgroupsController;
use App\Http\Controllers\Api\VideoconferenceController;
use App\Http\Controllers\Api\VideoconferenceStudgroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['as' => 'api.', 'middleware' => ['auth:sanctum', 'auth']], function() {
    Orion::resource('users', UserController::class)->withSoftDeletes();
    Orion::belongsToManyResource('users', 'studgroups', UserStudgroupsController::class);
    Orion::resource('studgroups', StudgroupController::class)->withSoftDeletes();
    Orion::resource('disciplines', DisciplineController::class)->withSoftDeletes();
    Orion::resource('themes', ThemeController::class)->withSoftDeletes();
    
    Orion::resource('questions', QuestionController::class)->withSoftDeletes();
    Orion::resource('answers', AnswerController::class)->withSoftDeletes();

    Orion::resource('tests', TestController::class)->withSoftDeletes();

    Orion::resource('testlogs', TestlogController::class);
    Orion::resource('assignments', AssignmentController::class)->withSoftDeletes();
    // TODO вомзможно не используется
    Orion::hasManyResource('assignments', 'testlogs', AssignmentTestlogController::class);

    Route::post('/assignments/studgroups', [AssignmentController::class, 'studgroups'])->name('api.assignments.studgroups');
    Route::post('/assignments/themes', [AssignmentController::class, 'themes'])->name('api.assignments.disciplines');

    Orion::resource('videoconferences', VideoconferenceController::class)->withSoftDeletes();
    Route::post('/videoconferences/{session}/chat', [VideoconferenceController::class, 'sendMessage'])->name('videoconferences.chat');
    Route::post('/videoconferences/{session}/answer', [VideoconferenceController::class, 'saveAnswer'])->name('videoconferences.answer');
    Route::post('/videoconferences/{session}/checking', [VideoconferenceController::class, 'addCheckControl'])->name('videoconferences.checking');
    Route::post('/videoconferences/{session}/action', [VideoconferenceController::class, 'addStudentAction'])->name('videoconferences.action');
    Route::post('/videoconferences/{session}/end', [VideoconferenceController::class, 'endCall'])->name('videoconferences.end');
    
    Orion::belongsToManyResource('videoconferences', 'studgroups', VideoconferenceStudgroupController::class);

    // получение данных студента
    Orion::resource('my-assignments', MyAssignmentController::class)->except(['batchStore', 'batchUpdate', 'store', 'update', 'destroy', 'restore', 'batchRestore', 'batchDestroy']);
    Route::post('/my-assignments/{testlog_id}/save', [MyAssignmentController::class, 'saveAnswer'])->name('my-assignments.saveAnswer');
    
    Orion::resource('my-videoconferences', MyVideoconferenceController::class)->except(['batchStore', 'batchUpdate', 'store', 'update', 'destroy', 'restore', 'batchRestore', 'batchDestroy']);

    Route::post('/upload', [FileController::class, 'upload']);
    Route::post('/delete-file', [FileController::class, 'delete']);
    Route::post('/upload-image', [QuestionController::class, 'upload']);
    Route::post('/delete-image', [QuestionController::class, 'deleteImage']);
});
