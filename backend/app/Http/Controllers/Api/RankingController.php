<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;

class RankingController extends Controller
{
    /**
     * 指定した大会IDのランキングを返す
     */
    public function index($tournamentId): JsonResponse
    {
        // 大会データをグループ・チーム・試合と一緒にロード
        $tournament = Tournament::with('groups.teams.gamesAsTeamA', 'groups.teams.gamesAsTeamB')
            ->findOrFail($tournamentId);

        $groups = $tournament->groups->map(function ($group) use ($tournament) {
            foreach ($group->teams as $team) {
                $played = $team->gamesAsTeamA
                    ->merge($team->gamesAsTeamB)
                    ->filter(fn($game) =>
                        $game->status === \App\Enums\GameStatus::Finished &&
                        $game->tournament_id === $tournament->id
                    );

                $stats = [
                    'match_played'  => $played->count(),
                    'win'           => 0,
                    'draw'          => 0,
                    'lose'          => 0,
                    'goals_for'     => 0,
                    'goals_against' => 0,
                    'points'        => 0,
                ];

                foreach ($played as $game) {
                    $isTeamA = $game->team_a_id === $team->id;
                    $gf = $isTeamA ? $game->team_a_score : $game->team_b_score;
                    $ga = $isTeamA ? $game->team_b_score : $game->team_a_score;

                    $stats['goals_for']     += $gf;
                    $stats['goals_against'] += $ga;

                    if ($gf > $ga) {
                        $stats['win']++;
                        $stats['points'] += 3;
                    } elseif ($gf === $ga) {
                        $stats['draw']++;
                        $stats['points'] += 1;
                    } else {
                        $stats['lose']++;
                    }
                }

                $stats['diff'] = $stats['goals_for'] - $stats['goals_against'];
                $team->stats = $stats;
            }

            // 勝ち点 → 得失点差 → 総得点の優先順位でソート
            $group->teams = $group->teams
                ->sortByDesc(fn($t) => $t->stats['goals_for'])
                ->sortByDesc(fn($t) => $t->stats['diff'])
                ->sortByDesc(fn($t) => $t->stats['points'])
                ->values();

            return $group;
        });

        return response()->json([
            'tournament' => [
                'id'   => $tournament->id,
                'name' => $tournament->name,
                'date' => $tournament->date,
            ],
            'groups' => $groups,
        ]);
    }
}
