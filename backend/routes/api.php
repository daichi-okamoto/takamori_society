<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\PlayerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// 認証ルーティング
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// チームルーティング
Route::middleware(['auth:sanctum', 'api'])->group(function () {
    Route::get('/teams', [TeamController::class, 'index']);     // 一覧取得
    Route::post('/teams', [TeamController::class, 'store']);     // 新規登録
    Route::put('/teams/{team}', [TeamController::class, 'update']);  // 更新
    Route::delete('/teams/{team}', [TeamController::class, 'destroy']); // 削除
});

Route::post('/teams_test', function () {
    return response()->json(['message' => 'POST受け取り成功']);
});

// グループルーティング
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/groups', [GroupController::class, 'index']);    // 一覧取得
    Route::post('/groups', [GroupController::class, 'store']);   // 新規登録
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('players', PlayerController::class);
});