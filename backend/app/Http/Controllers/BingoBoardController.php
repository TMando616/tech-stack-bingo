<?php

namespace App\Http\Controllers;

use App\Models\BingoBoard;
use App\Models\BingoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class BingoBoardController extends Controller
{
    /**
     * ログインユーザーのビンゴボード一覧を取得
     * 
     * @return Collection
     */
    public function index(): Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->bingoBoards()->orderBy('created_at', 'desc')->get();
    }

    /**
     * 新しいビンゴボードと25個のマス目を作成
     * 
     * @param Request $request
     * @return BingoBoard
     */
    public function store(Request $request): BingoBoard
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return DB::transaction(function () use ($request, $user) {
            // 1. ボードの作成
            $board = $user->bingoBoards()->create([
                'title' => $request->title,
            ]);

            // 2. 25個の空のマス目を作成 (バルクインサートで最適化)
            $items = [];
            $now = now()->toDateTimeString();
            for ($i = 0; $i < 25; $i++) {
                $isFree = ($i === 12);
                $items[] = [
                    'bingo_board_id' => $board->id,
                    'label' => $isFree ? 'FREE' : '',
                    'position' => $i,
                    'is_achieved' => $isFree,
                    'achieved_at' => $isFree ? now()->toDateString() : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            BingoItem::insert($items);

            return $board->load('items');
        });
    }
}
