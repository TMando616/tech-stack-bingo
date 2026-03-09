<?php

namespace Database\Seeders;

use App\Models\BingoItem;
use Illuminate\Database\Seeder;

class BingoItemSeeder extends Seeder
{
    /**
     * データベースの初期データを投入
     * 5x5(25マス)のビンゴ項目を作成します。
     */
    public function run(): void
    {
        // 5x5のサンプル技術スタック（中央はFREE）
        $techStacks = [
            'HTML', 'CSS', 'JavaScript', 'TypeScript', 'React',
            'Vue.js', 'Next.js', 'Nuxt.js', 'Node.js', 'Express',
            'PHP', 'Laravel', 'FREE', 'Python', 'FastAPI',
            'Go', 'Gin', 'Rust', 'Docker', 'Git',
            'MySQL', 'PostgreSQL', 'Redis', 'AWS', 'Vercel'
        ];

        foreach ($techStacks as $index => $label) {
            $isFree = ($index === 12); // 中央(25マスの真ん中)

            BingoItem::create([
                'label' => $label,
                'position' => $index,
                'is_achieved' => $isFree, // FREEは最初から達成済み
                'achieved_at' => $isFree ? now()->toDateString() : null,
            ]);
        }
    }
}
