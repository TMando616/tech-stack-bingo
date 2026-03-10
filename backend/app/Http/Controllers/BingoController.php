<?php

namespace App\Http\Controllers;

use App\Http\Resources\BingoItemResource;
use App\Models\BingoItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BingoController extends Controller
{
    /**
     * ログインユーザーのビンゴ項目を全件取得
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        // ログイン中のユーザーに紐づくビンゴ項目のみを取得
        return BingoItemResource::collection(
            auth()->user()->bingoItems()->orderBy('position')->get()
        );
    }

    /**
     * ビンゴ項目の更新
     * ログインユーザーが所有する項目のみを更新可能にします。
     *
     * @param Request $request
     * @param BingoItem $bingoItem
     * @return BingoItemResource
     */
    public function update(Request $request, BingoItem $bingoItem): BingoItemResource
    {
        // 所有権の確認
        if ($bingoItem->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = [];

        // 達成フラグの更新
        if ($request->has('is_achieved')) {
            $isAchieved = (bool) $request->input('is_achieved');
            $data['is_achieved'] = $isAchieved;
            $data['achieved_at'] = $isAchieved ? now()->toDateString() : null;
        }

        // ラベルの更新 (中央以外)
        if ($request->has('label') && $bingoItem->position !== 12) {
            $data['label'] = $request->input('label');
        }

        $bingoItem->update($data);

        return new BingoItemResource($bingoItem);
    }
}
