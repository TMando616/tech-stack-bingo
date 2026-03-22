<?php

namespace Tests\Unit;

use App\Models\BingoBoard;
use App\Models\BingoItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BingoBoardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ビンゴ数の再計算ロジックが正しいことをテスト
     */
    public function test_recalculate_bingo_count(): void
    {
        $user = User::factory()->create();
        $board = BingoBoard::create([
            'user_id' => $user->id,
            'title' => 'Test Board',
        ]);

        // 25個のアイテムを作成 (すべて未達成)
        $items = [];
        for ($i = 0; $i < 25; $i++) {
            $items[] = BingoItem::create([
                'bingo_board_id' => $board->id,
                'label' => 'Item ' . $i,
                'position' => $i,
                'is_achieved' => false,
            ]);
        }

        // 0 ビンゴであることを確認
        $this->assertEquals(0, $board->recalculateBingoCount());

        // 1列目 (0, 1, 2, 3, 4) を達成にする
        foreach ([0, 1, 2, 3, 4] as $pos) {
            BingoItem::where('bingo_board_id', $board->id)->where('position', $pos)->update(['is_achieved' => true]);
        }
        
        // 1 ビンゴであることを確認
        $this->assertEquals(1, $board->recalculateBingoCount());

        // 斜め (0, 6, 12, 18, 24) を達成にする
        foreach ([0, 6, 12, 18, 24] as $pos) {
            BingoItem::where('bingo_board_id', $board->id)->where('position', $pos)->update(['is_achieved' => true]);
        }
        
        // 1列目と斜めがあるので、2 ビンゴであることを確認
        // (注: 0 は重複しているがカウントされる)
        $this->assertEquals(2, $board->recalculateBingoCount());
    }

    /**
     * アイテムが不足している場合は 0 を返すことをテスト
     */
    public function test_recalculate_bingo_count_with_insufficient_items(): void
    {
        $user = User::factory()->create();
        $board = BingoBoard::create([
            'user_id' => $user->id,
            'title' => 'Incomplete Board',
        ]);

        // アイテムを10個だけ作成
        for ($i = 0; $i < 10; $i++) {
            BingoItem::create([
                'bingo_board_id' => $board->id,
                'label' => 'Item ' . $i,
                'position' => $i,
                'is_achieved' => true,
            ]);
        }

        $this->assertEquals(0, $board->recalculateBingoCount());
    }
}
