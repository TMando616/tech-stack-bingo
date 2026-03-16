<?php

namespace App\Http\Controllers;

use App\Http\Resources\BingoBoardResource;
use App\Models\BingoBoard;
use App\Models\BingoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BingoBoardController extends Controller
{
    /**
     * ログインユーザーのビンゴボード一覧を取得
     * 
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return BingoBoardResource::collection(
            $user->bingoBoards()->with('items')->orderBy('created_at', 'desc')->get()
        );
    }

    /**
     * 新しいビンゴボードと25個のマス目を作成
     * 
     * @param Request $request
     * @return BingoBoardResource
     */
    public function store(Request $request): BingoBoardResource
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'template' => 'nullable|string|in:' . implode(',', array_keys(BingoBoard::TEMPLATES)),
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $board = DB::transaction(function () use ($request, $user) {
            // 1. ボードの作成
            $board = $user->bingoBoards()->create([
                'title' => $request->title,
            ]);

            // 2. テンプレート項目の取得
            $templateItems = [];
            if ($request->template && isset(BingoBoard::TEMPLATES[$request->template])) {
                $templateItems = BingoBoard::TEMPLATES[$request->template]['items'];
                shuffle($templateItems);
            }

            // 3. 25個のマス目を作成 (バルクインサートで最適化)
            $items = [];
            $now = now()->toDateTimeString();
            for ($i = 0; $i < 25; $i++) {
                $isFree = ($i === 12);
                $label = $isFree ? 'FREE' : '';
                
                // テンプレートがある場合、FREE以外のマスに埋める
                if (!$isFree && !empty($templateItems)) {
                    $label = array_shift($templateItems) ?? '';
                }

                $items[] = [
                    'bingo_board_id' => $board->id,
                    'label' => $label,
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

        return new BingoBoardResource($board);
    }

    /**
     * 公開用の share_id からボード情報を取得 (認証不要)
     * 
     * @param string $shareId
     * @return BingoBoardResource
     */
    public function showShared(string $shareId): BingoBoardResource
    {
        $board = BingoBoard::where('share_id', $shareId)->firstOrFail();
        return new BingoBoardResource($board->load('items'));
    }
}
