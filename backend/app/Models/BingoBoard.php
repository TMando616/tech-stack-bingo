<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Str;

class BingoBoard extends Model
{
    use HasFactory;

    /**
     * モデルの初期起動処理
     */
    protected static function booted()
    {
        static::creating(function ($board) {
            $board->share_id = (string) Str::uuid();
        });
    }

    /**
     * 技術スタックテンプレート
     */
    const TEMPLATES = [
        'frontend' => [
            'name' => 'Frontend',
            'items' => [
                'HTML5', 'CSS3', 'TypeScript', 'Vue.js', 'React',
                'Next.js', 'Nuxt.js', 'Vite', 'Webpack', 'Babel',
                'ESLint', 'Prettier', 'Tailwind CSS', 'Sass', 'Testing Library',
                'Jest', 'Vitest', 'Cypress', 'Playwright', 'PWA',
                'WebAssembly', 'Service Worker', 'WebSocket', 'GraphQL'
            ]
        ],
        'backend' => [
            'name' => 'Backend',
            'items' => [
                'PHP', 'Laravel', 'Python', 'FastAPI', 'Node.js',
                'Express', 'Go', 'Gin', 'Rust', 'Actix Web',
                'MySQL', 'PostgreSQL', 'Redis', 'MongoDB', 'Docker',
                'Kubernetes', 'AWS', 'GCP', 'Azure', 'Terraform',
                'Nginx', 'Apache', 'CI/CD', 'OpenAPI'
            ]
        ]
    ];

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'title',
        'theme',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
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
     * このボードに付いた「いいね」を取得
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
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
