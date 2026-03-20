<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BingoBoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'share_id' => $this->share_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'theme' => $this->theme,
            'bingo_count' => $this->bingo_count,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'items' => BingoItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
