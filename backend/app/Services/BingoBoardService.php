<?php

namespace App\Services;

use App\Models\BingoBoard;
use App\Models\BingoItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BingoBoardService
{
    /**
     * 新しいビンゴボードと25個のマス目を作成
     * 
     * @param User $user
     * @param array $data
     * @return BingoBoard
     */
    public function createBoard(User $user, array $data): BingoBoard
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. ボードの作成
            $board = $user->bingoBoards()->create([
                'title' => $data['title'],
                'theme' => $data['theme'] ?? 'default',
            ]);

            // 2. テンプレート項目の取得
            $templateItems = [];
            if (isset($data['template']) && isset(BingoBoard::TEMPLATES[$data['template']])) {
                $templateItems = BingoBoard::TEMPLATES[$data['template']]['items'];
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
    }

    /**
     * ビンゴボードの更新
     * 
     * @param BingoBoard $board
     * @param array $data
     * @return BingoBoard
     */
    public function updateBoard(BingoBoard $board, array $data): BingoBoard
    {
        $board->update($data);
        return $board->load('items');
    }

    /**
     * 指定されたビンゴボードを削除
     * 
     * @param BingoBoard $board
     * @return bool|null
     */
    public function deleteBoard(BingoBoard $board)
    {
        return $board->delete();
    }
}
