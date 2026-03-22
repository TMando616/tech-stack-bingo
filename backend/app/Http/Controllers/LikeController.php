<?php

namespace App\Http\Controllers;

use App\Models\BingoBoard;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * ボードに「いいね」をする
     */
    public function store(Request $request, BingoBoard $bingoBoard)
    {
        // 公開されているボードまたは自分のボードのみいいね可能
        if (!$bingoBoard->is_public && $bingoBoard->user_id !== Auth::id()) {
            abort(403);
        }

        Like::firstOrCreate([
            'user_id' => Auth::id(),
            'bingo_board_id' => $bingoBoard->id,
        ]);

        return response()->json([
            'likes_count' => $bingoBoard->likes()->count(),
            'is_liked' => true
        ]);
    }

    /**
     * 「いいね」を解除する
     */
    public function destroy(Request $request, BingoBoard $bingoBoard)
    {
        Like::where('user_id', Auth::id())
            ->where('bingo_board_id', $bingoBoard->id)
            ->delete();

        return response()->json([
            'likes_count' => $bingoBoard->likes()->count(),
            'is_liked' => false
        ]);
    }
}
