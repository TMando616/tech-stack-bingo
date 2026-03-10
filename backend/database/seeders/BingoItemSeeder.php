<?php

namespace Database\Seeders;

use App\Models\BingoItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BingoItemSeeder extends Seeder
{
    /**
     * データベースの初期データを投入
     */
    public function run(): void
    {
        // テストユーザーを作成
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

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
                'user_id' => $user->id,
                'label' => $label,
                'position' => $index,
                'is_achieved' => $isFree,
                'achieved_at' => $isFree ? now()->toDateString() : null,
            ]);
        }
    }
}
