<?php

namespace Database\Seeders;

use App\Models\BingoBoard;
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

        // ボード1: Web開発
        $board1 = BingoBoard::create([
            'user_id' => $user->id,
            'title' => 'Web開発スキル',
        ]);

        $techStacks1 = [
            'HTML', 'CSS', 'JavaScript', 'TypeScript', 'React',
            'Vue.js', 'Next.js', 'Nuxt.js', 'Node.js', 'Express',
            'PHP', 'Laravel', 'FREE', 'Python', 'FastAPI',
            'Go', 'Gin', 'Rust', 'Docker', 'Git',
            'MySQL', 'PostgreSQL', 'Redis', 'AWS', 'Vercel'
        ];

        foreach ($techStacks1 as $index => $label) {
            $isFree = ($index === 12);
            BingoItem::create([
                'bingo_board_id' => $board1->id,
                'label' => $label,
                'position' => $index,
                'is_achieved' => $isFree,
                'achieved_at' => $isFree ? now()->toDateString() : null,
            ]);
        }

        // ボード2: インフラ・資格
        $board2 = BingoBoard::create([
            'user_id' => $user->id,
            'title' => 'インフラ・ツール',
        ]);

        $techStacks2 = [
            'Linux', 'Nginx', 'Apache', 'Docker', 'Kubernetes',
            'AWS', 'Azure', 'GCP', 'Terraform', 'Ansible',
            'Git', 'GitHub', 'FREE', 'Jenkins', 'CircleCI',
            'Sentry', 'Datadog', 'NewRelic', 'Grafana', 'Prometheus',
            'CloudFront', 'Route53', 'Lambda', 'S3', 'RDS'
        ];

        foreach ($techStacks2 as $index => $label) {
            $isFree = ($index === 12);
            BingoItem::create([
                'bingo_board_id' => $board2->id,
                'label' => $label,
                'position' => $index,
                'is_achieved' => $isFree,
                'achieved_at' => $isFree ? now()->toDateString() : null,
            ]);
        }
    }
}
