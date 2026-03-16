<?php

namespace App\Http\Controllers;

use App\Http\Resources\BingoItemResource;
use App\Models\BingoBoard;
use App\Models\BingoItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class BingoController extends Controller
{
    /**
     * 指定されたボードのビンゴ項目を取得
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'bingo_board_id' => 'required|exists:bingo_boards,id',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $board = $user->bingoBoards()->findOrFail($request->bingo_board_id);

        return BingoItemResource::collection(
            $board->items()->orderBy('position')->get()
        );
    }

    /**
     * ビンゴ項目の更新
     *
     * @param Request $request
     * @param BingoItem $bingoItem
     * @return BingoItemResource
     */
    public function update(Request $request, BingoItem $bingoItem): BingoItemResource
    {
        $request->validate([
            'is_achieved' => 'sometimes|boolean',
            'label' => 'sometimes|string|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        // ボード経由で所有権を確認
        $user->bingoBoards()->findOrFail($bingoItem->bingo_board_id);

        $data = [];

        if ($request->has('is_achieved')) {
            $isAchieved = (bool) $request->input('is_achieved');
            $data['is_achieved'] = $isAchieved;
            $data['achieved_at'] = $isAchieved ? now()->toDateString() : null;
        }

        if ($request->has('label') && $bingoItem->position !== 12) {
            // 文字列として保存 (空文字も許可する場合はそのまま)
            $data['label'] = (string) $request->input('label');
        }

        if (!empty($data)) {
            $bingoItem->update($data);
            
            // ビンゴ数の再計算
            if (isset($data['is_achieved'])) {
                $bingoItem->bingoBoard->recalculateBingoCount();
            }
        }

        return new BingoItemResource($bingoItem);
    }
}
