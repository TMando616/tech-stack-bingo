<?php

use App\Http\Controllers\BingoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ユーザー情報取得 (Breeze標準)
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// ビンゴ項目に関連するAPI (認証必須)
Route::middleware(['auth:sanctum'])->prefix('bingo-items')->group(function () {
    // 一覧取得
    Route::get('/', [BingoController::class, 'index']);
    // 更新
    Route::patch('/{bingoItem}', [BingoController::class, 'update']);
});

// 認証関連のルート (login, register, logout など)
require __DIR__.'/auth.php';
