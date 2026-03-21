<?php

namespace App\Http\Controllers;

use App\Http\Resources\BingoItemResource;
use App\Models\BingoBoard;
use App\Models\BingoItem;
use App\Services\BingoItemService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BingoController extends Controller
{
    protected $itemService;

    public function __construct(BingoItemService $itemService)
    {
        $this->itemService = $itemService;
    }

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
            'description' => 'sometimes|string|nullable|max:1000',
            'link' => 'sometimes|string|nullable|url|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        // ボード経由で所有権を確認
        $user->bingoBoards()->findOrFail($bingoItem->bingo_board_id);

        $item = $this->itemService->updateItem($bingoItem, $request->all());

        return new BingoItemResource($item);
    }
}
