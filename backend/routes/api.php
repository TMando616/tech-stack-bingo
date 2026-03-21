<?php

use App\Http\Controllers\BingoBoardController;
use App\Http\Controllers\BingoController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ユーザー情報取得
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// 認証必須のAPI
Route::middleware(['auth:sanctum'])->group(function () {
    // ビンゴボード管理
    Route::prefix('bingo-boards')->group(function () {
        Route::get('/', [BingoBoardController::class, 'index']);
        Route::post('/', [BingoBoardController::class, 'store']);
        Route::patch('/{bingoBoard}', [BingoBoardController::class, 'update']);
        Route::delete('/{bingoBoard}', [BingoBoardController::class, 'destroy']);
    });

    // ビンゴ項目管理
    Route::prefix('bingo-items')->group(function () {
        Route::get('/', [BingoController::class, 'index']);
        Route::patch('/{bingoItem}', [BingoController::class, 'update']);
    });

    // 統計分析
    Route::get('/analytics/summary', [AnalyticsController::class, 'index']);
});

// 公開API
Route::get('/bingo-boards/share/{shareId}', [BingoBoardController::class, 'showShared']);

// 認証関連
require __DIR__.'/auth.php';
