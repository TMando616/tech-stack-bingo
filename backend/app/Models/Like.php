<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'bingo_board_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bingoBoard(): BelongsTo
    {
        return $this->belongsTo(BingoBoard::class);
    }
}
