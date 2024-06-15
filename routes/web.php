<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\MyAssignmentController;
use App\Http\Controllers\MyVideoconferenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\VideoconferenceController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/connection/{sessionId}', [ConferenceController::class, 'connectToSession']);
Route::post('/create-session', [ConferenceController::class, 'createSession']);

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');


Route::group(['prefix' => 'admin'], function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('admin.edit');
    Route::get('/new/{role}', [AdminController::class, 'create'])->name('admin.new');
})->middleware(['auth']);


Route::group(['prefix' => 'reference'], function () {
    Route::get('/studgroups', [AdminController::class, 'studgroups'])->name('admin.reference.studgroups');
    Route::get('/disciplines', [AdminController::class, 'disciplines'])->name('admin.reference.disciplines');
    Route::get('/themes', [AdminController::class, 'themes'])->name('admin.reference.themes');
})->middleware(['auth']);

Route::group(['prefix' => 'questions'], function () {
    Route::get('/', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/edit/{id}', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::get('/new', [QuestionController::class, 'create'])->name('questions.new');
})->middleware(['auth']);

Route::group(['prefix' => 'tests'], function () {
    Route::get('/', [TestController::class, 'index'])->name('tests.index');
    Route::get('/edit/{id}', [TestController::class, 'edit'])->name('tests.edit');
    Route::get('/new', [TestController::class, 'create'])->name('tests.new');
})->middleware(['auth']);

Route::group(['prefix' => 'assignments'], function () {
    Route::get('/', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/edit/{id}', [AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::get('/new', [AssignmentController::class, 'create'])->name('assignments.new');

    Route::get('/my', [MyAssignmentController::class, 'index'])->name('assignments.my');
    Route::get('/testing/{testlog_id}', [MyAssignmentController::class, 'testing'])->name('assignments.testing');
})->middleware(['auth']);

Route::group(['prefix' => 'videoconferences', 'middleware' => ['auth']], function () {
    Route::get('/', [VideoconferenceController::class, 'index'])->name('videoconferences.index');
    Route::get('/edit/{id}', [VideoconferenceController::class, 'edit'])->name('videoconferences.edit');
    Route::get('/new', [VideoconferenceController::class, 'create'])->name('videoconferences.new');
    Route::get('/room/{session}', [VideoconferenceController::class, 'room'])->name('videoconferences.room');
    
    Route::get('/my', [MyVideoconferenceController::class, 'index'])->name('videoconferences.my');
    Route::get('/detail/{vc_id}', [VideoconferenceController::class, 'detail'])->name('videoconferences.detail');
    Route::get('/my/detail/{vc_id}', [ReportController::class, 'detailStudent'])->name('videoconferences.my.detail');
});

Route::group(['prefix' => 'report', 'middleware' => ['auth']], function () {
    Route::get('/student/{testlog_id}', [ReportController::class, 'student'])->name('report.student');
    Route::get('/assignment/{assignment_id}', [ReportController::class, 'assignment'])->name('report.assignment');
    Route::get('/detail/{testlog_id}', [ReportController::class, 'detail'])->name('report.detail');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);

    return ['token' => $token->plainTextToken];
});

require __DIR__ . '/auth.php';
