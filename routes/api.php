<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AssignmentTestlogController;
use App\Http\Controllers\Api\DisciplineController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\StudgroupController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\TestlogController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserStudgroupsController;
use App\Http\Controllers\Api\VideoconferenceController;
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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/test', function(Request $request) {
        return '111';
    });
});

Route::group(['as' => 'api.'], function() {
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

});
