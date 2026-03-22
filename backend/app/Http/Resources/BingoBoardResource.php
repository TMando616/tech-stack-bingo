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
            'is_public' => (bool) $this->is_public,
            'bingo_count' => $this->bingo_count,
            'likes_count' => $this->likes()->count(),
            'is_liked' => $request->user() ? $this->likes()->where('user_id', $request->user()->id)->exists() : false,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'items' => BingoItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
