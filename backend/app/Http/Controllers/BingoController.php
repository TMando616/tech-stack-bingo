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
     * ビンゴ項目の更新
     * 達成状況(is_achieved)およびラベル(label)を更新します。
     *
     * @param Request $request
     * @param BingoItem $bingoItem
     * @return BingoItemResource
     */
    public function update(Request $request, BingoItem $bingoItem): BingoItemResource
    {
        // 更新データの構築
        $data = [];

        // 達成フラグの更新がある場合
        if ($request->has('is_achieved')) {
            $isAchieved = (bool) $request->input('is_achieved');
            $data['is_achieved'] = $isAchieved;
            // 達成された場合は今日の日付を設定、解除された場合はNULLを設定
            $data['achieved_at'] = $isAchieved ? now()->toDateString() : null;
        }

        // ラベルの更新がある場合 (中央のFREE以外)
        if ($request->has('label') && $bingoItem->position !== 12) {
            $data['label'] = $request->input('label');
        }

        $bingoItem->update($data);

        return new BingoItemResource($bingoItem);
    }
}
