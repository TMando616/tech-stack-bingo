<?php

namespace App\Http\Controllers;

use App\Models\BingoBoard;
use App\Models\BingoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BingoBoardController extends Controller
{
    /**
     * ログインユーザーのビンゴボード一覧を取得
     */
    public function index()
    {
        return auth()->user()->bingoBoards()->orderBy('created_at', 'desc')->get();
    }

    /**
     * 新しいビンゴボードと25個のマス目を作成
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. ボードの作成
            $board = auth()->user()->bingoBoards()->create([
                'title' => $request->title,
            ]);

            // 2. 25個の空のマス目を作成
            for ($i = 0; $i < 25; $i++) {
                BingoItem::create([
                    'bingo_board_id' => $board->id,
                    'label' => ($i === 12) ? 'FREE' : '',
                    'position' => $i,
                    'is_achieved' => ($i === 12),
                    'achieved_at' => ($i === 12) ? now()->toDateString() : null,
                ]);
            }

            return $board->load('items');
        });
    }
}
