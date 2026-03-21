<?php

namespace App\Services;

use App\Models\BingoBoard;
use App\Models\BingoItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * 全ユーザーの達成数が多い技術ラベルのランキングを取得
     * 
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getGlobalTrends(int $limit = 10)
    {
        return BingoItem::select('label', DB::raw('count(*) as count'))
            ->where('is_achieved', true)
            ->where('label', '!=', 'FREE')
            ->where('label', '!=', '')
            ->groupBy('label')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ユーザーの進捗状況を全体平均と比較
     * 
     * @param User $user
     * @return array
     */
    public function getUserPerformance(User $user)
    {
        // CTEを使用して全ユーザーの平均ビンゴ数を算出
        $stats = DB::select("
            WITH user_bingo_counts AS (
                SELECT user_id, SUM(bingo_count) as total_bingos
                FROM bingo_boards
                GROUP BY user_id
            )
            SELECT 
                AVG(total_bingos) as avg_bingos,
                MAX(total_bingos) as max_bingos
            FROM user_bingo_counts
        ")[0];

        $userTotalBingo = $user->bingoBoards()->sum('bingo_count');

        return [
            'user_total_bingos' => (int) $userTotalBingo,
            'average_bingos' => round($stats->avg_bingos ?? 0, 2),
            'max_bingos' => (int) ($stats->max_bingos ?? 0),
            'rank_info' => $this->getUserRankInfo($user, $userTotalBingo)
        ];
    }

    /**
     * ユーザーの順位情報を取得
     */
    private function getUserRankInfo(User $user, int $userTotalBingo)
    {
        $betterUsersCount = DB::table('bingo_boards')
            ->select('user_id')
            ->groupBy('user_id')
            ->having(DB::raw('SUM(bingo_count)'), '>', $userTotalBingo)
            ->count();

        return [
            'rank' => $betterUsersCount + 1,
            'total_users' => User::count()
        ];
    }

    /**
     * 「次の一手」レコメンド
     * 自分が未達成の技術のうち、他ユーザーが最も多く達成しているものを推奨
     * 
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public function getRecommendations(User $user)
    {
        // ユーザーが既に達成したラベルを取得
        $myAchievedLabels = BingoItem::join('bingo_boards', 'bingo_items.bingo_board_id', '=', 'bingo_boards.id')
            ->where('bingo_boards.user_id', $user->id)
            ->where('bingo_items.is_achieved', true)
            ->pluck('label')
            ->unique()
            ->toArray();

        // 他のユーザーが達成しているが、自分がまだ達成していないラベルをランキング形式で取得
        return BingoItem::select('label', DB::raw('count(DISTINCT bingo_boards.user_id) as user_count'))
            ->join('bingo_boards', 'bingo_items.bingo_board_id', '=', 'bingo_boards.id')
            ->where('bingo_items.is_achieved', true)
            ->where('bingo_boards.user_id', '!=', $user->id)
            ->whereNotIn('label', array_merge(['FREE', ''], $myAchievedLabels))
            ->groupBy('label')
            ->orderBy('user_count', 'desc')
            ->limit(5)
            ->get();
    }
}
