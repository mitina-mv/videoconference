<?php

use App\Http\Controllers\Api\DisciplineController;
use App\Http\Controllers\Api\StudgroupController;
use App\Http\Controllers\Api\UserController;
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
    Orion::resource('studgroups', StudgroupController::class)->withSoftDeletes();
    Orion::resource('disciplines', DisciplineController::class)->withSoftDeletes();
});
