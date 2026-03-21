<?php

namespace App\Services;

use App\Models\BingoItem;

class BingoItemService
{
    /**
     * ビンゴ項目の更新
     * 
     * @param BingoItem $item
     * @param array $data
     * @return BingoItem
     */
    public function updateItem(BingoItem $item, array $data): BingoItem
    {
        $updateData = [];

        if (isset($data['is_achieved'])) {
            $isAchieved = (bool) $data['is_achieved'];
            $updateData['is_achieved'] = $isAchieved;
            $updateData['achieved_at'] = $isAchieved ? now()->toDateString() : null;
        }

        if ($item->position !== 12) {
            if (isset($data['label'])) {
                $updateData['label'] = $data['label'];
            }
            if (array_key_exists('description', $data)) {
                $updateData['description'] = $data['description'];
            }
            if (array_key_exists('link', $data)) {
                $updateData['link'] = $data['link'];
            }
        }

        if (!empty($updateData)) {
            $item->update($updateData);
            
            // ビンゴ数の再計算
            if (isset($updateData['is_achieved'])) {
                $item->bingoBoard->recalculateBingoCount();
            }
        }

        return $item;
    }
}
