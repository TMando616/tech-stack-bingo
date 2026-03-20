<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BingoItem extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'bingo_board_id',
        'label',
        'description',
        'link',
        'is_achieved',
        'achieved_at',
        'position',
    ];

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'is_achieved' => 'boolean',
        'achieved_at' => 'date:Y-m-d',
    ];

    /**
     * この項目を所有するビンゴボードを取得
     */
    public function bingoBoard(): BelongsTo
    {
        return $this->belongsTo(BingoBoard::class);
    }
}
