<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BingoItemResource extends JsonResource
{
    /**
     * リソースを配列に変換します。
     * APIレスポンスで返却するデータの形式を定義します。
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'is_achieved' => $this->is_achieved,
            'achieved_at' => $this->achieved_at ? $this->achieved_at->format('Y-m-d') : null,
            'position' => (int) $this->position,
        ];
    }
}
