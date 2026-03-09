<?php

use App\Http\Controllers\BingoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| アプリケーションの全APIルートをここで定義します。
|
*/

// ビンゴ項目に関連するAPI
Route::prefix('bingo-items')->group(function () {
    // 一覧取得 (GET /api/bingo-items)
    Route::get('/', [BingoController::class, 'index']);
    // 達成状況の更新 (PATCH /api/bingo-items/{bingoItem})
    Route::patch('/{bingoItem}', [BingoController::class, 'update']);
});
