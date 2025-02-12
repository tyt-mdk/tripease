<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CandidateDateController;
use App\Http\Controllers\TripRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RequestCommentController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\JoinTripController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 認証不要のルート
Route::get('/', function () {
    return view('trips.toppage');
})->name('toppage');

// 認証関連のルート
Auth::routes();

// 未ログインユーザーのみアクセス可能
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// 認証が必要なルート
Route::middleware(['auth'])->group(function () {
    // ダッシュボード
    Route::get('/trips/dashboard', [UserController::class, 'index'])->name('dashboard');
    
    // 参加中の旅行計画一覧
    Route::get('/trips/participating', [TripController::class, 'participating'])
        ->name('trips.participating');

    // トリップの基本的なCRUD操作
    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
    Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');

    // 基本的なトリップ表示（概要・日程調整を含む）
    Route::get('/trips/{trip}', [TripController::class, 'eachplanning'])->name('trips.show');
    Route::get('/trips/{trip}/planning', [TripController::class, 'eachplanning'])->name('trips.planning');
    Route::get('/trips/{trip}/eachplanning', [TripController::class, 'eachplanning'])->name('trips.eachplanning');
    
    // 共有リンク関連
    Route::post('/trips/{trip}/share', [TripController::class, 'generateShareLink'])
        ->name('trips.generateShareLink');
    Route::get('/trips/join/{token}', [TripController::class, 'showJoinConfirmation'])
        ->name('trips.join');
    Route::post('/trips/join/{token}/confirm', [TripController::class, 'joinByToken'])
        ->name('trips.join.confirm');
    Route::post('/trips/join', [JoinTripController::class, 'joinViaUrl'])
        ->name('trips.join.url');

    // 日程調整関連
    Route::prefix('trips/{trip}/schedule')->group(function () {
        Route::get('/', [ScheduleController::class, 'showDatePlanning'])->name('trips.schedule');
        Route::post('/add-date', [ScheduleController::class, 'addCandidateDate'])->name('schedule.addDate');
        Route::post('/finalize', [ScheduleController::class, 'finalizeSchedule'])->name('schedule.finalize');
        Route::post('/vote-date', [ScheduleController::class, 'voteDate'])->name('schedule.voteDate');
    });

    // 候補日関連
    Route::prefix('trips/{trip}/candidate-dates')->group(function () {
        Route::post('/', [CandidateDateController::class, 'store'])->name('candidate-dates.store');
        Route::delete('/{candidateDate}', [CandidateDateController::class, 'destroy'])->name('candidate-dates.destroy');
    });
    Route::get('/get-candidate-dates', [CandidateDateController::class, 'getCandidateDates']);
    Route::post('/set-judgement', [CandidateDateController::class, 'setJudgement']);

    // 要望関連
    Route::get('/trips/{trip}/request', [TripRequestController::class, 'index'])->name('trips.request');

    // 要望のリクエスト関連
    Route::prefix('trip-requests')->group(function () {
        Route::post('/', [TripRequestController::class, 'store'])->name('requests.store');
        Route::post('/{request}/comment', [TripRequestController::class, 'storeComment'])->name('requests.comment');
        Route::post('/{request}/like', [TripRequestController::class, 'toggleLike'])->name('requests.like');
        Route::put('/{tripRequest}', [TripRequestController::class, 'update'])->name('requests.update');
        Route::delete('/{request}', [TripRequestController::class, 'destroy'])->name('requests.destroy');
    });

    // コメント関連
    Route::prefix('request-comments')->group(function () {
        Route::put('/{comment}', [RequestCommentController::class, 'update'])->name('request.comments.update');
        Route::delete('/{comment}', [RequestCommentController::class, 'destroy'])->name('request.comments.destroy');
    });

    // 旅程ノート関連
    Route::prefix('trips/{trip}/itinerary')->group(function () {
        Route::get('/', [ItineraryController::class, 'index'])->name('trips.itinerary.index');
        Route::post('/', [ItineraryController::class, 'store'])->name('trips.itinerary.store');
        Route::put('/{itinerary}', [ItineraryController::class, 'update'])->name('trips.itinerary.update');
        Route::delete('/{itinerary}', [ItineraryController::class, 'destroy'])->name('trips.itinerary.destroy');
        Route::post('/order', [ItineraryController::class, 'updateOrder'])->name('trips.itinerary.order');
    });

    // 日程の更新
    Route::patch('/trips/{trip}/update-dates', [TripController::class, 'updateDates'])->name('trips.update-dates');
});

// ログアウト
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');