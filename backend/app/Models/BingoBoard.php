<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BingoBoard extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'title',
    ];

    /**
     * このボードを所有するユーザーを取得
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ボード内の全マス目を取得
     */
    public function items(): HasMany
    {
        return $this->hasMany(BingoItem::class);
    }

    /**
     * ビンゴ数を再計算し、保存する
     */
    public function recalculateBingoCount(): int
    {
        $items = $this->items()->orderBy('position')->get();
        if ($items->count() < 25) {
            return 0;
        }

        $count = 0;
        $lines = [
            [0, 1, 2, 3, 4], [5, 6, 7, 8, 9], [10, 11, 12, 13, 14], [15, 16, 17, 18, 19], [20, 21, 22, 23, 24], // 横
            [0, 5, 10, 15, 20], [1, 6, 11, 16, 21], [2, 7, 12, 17, 22], [3, 8, 13, 18, 23], [4, 9, 14, 19, 24], // 縦
            [0, 6, 12, 18, 24], [4, 8, 12, 16, 20] // 斜め
        ];

        foreach ($lines as $line) {
            $isComplete = true;
            foreach ($line as $index) {
                if (!$items[$index]->is_achieved) {
                    $isComplete = false;
                    break;
                }
            }
            if ($isComplete) {
                $count++;
            }
        }

        $this->bingo_count = $count;
        $this->save();

        return $count;
    }
}
