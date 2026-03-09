<?php

namespace App\Http\Controllers;

use App\Http\Resources\BingoItemResource;
use App\Models\BingoItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BingoController extends Controller
{
    /**
     * ビンゴ項目の全件取得
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        // 全てのビンゴ項目を取得し、リソースクラスを使用して整形して返却します。
        return BingoItemResource::collection(BingoItem::orderBy('position')->get());
    }

    /**
     * ビンゴ項目の達成状況の更新
     * 達成フラグ(is_achieved)を更新し、達成された場合は達成日を自動設定します。
     *
     * @param Request $request
     * @param BingoItem $bingoItem
     * @return BingoItemResource
     */
    public function update(Request $request, BingoItem $bingoItem): BingoItemResource
    {
        // リクエストの達成フラグに基づいてデータを更新
        $isAchieved = (bool) $request->input('is_achieved');

        $bingoItem->update([
            'is_achieved' => $isAchieved,
            // 達成された場合は今日の日付を設定、解除された場合はNULLを設定
            'achieved_at' => $isAchieved ? now()->toDateString() : null,
        ]);

        return new BingoItemResource($bingoItem);
    }
}
