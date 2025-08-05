<?php

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Tournament;

class ViewTournament extends ViewRecord
{
    protected static string $resource = TournamentResource::class;
    protected static string $view = 'tournament.view-tournament';
    public $groups;

    public function mount($record): void
    {
        parent::mount($record);

        $tournament = $this->record->load('groups.teams.gamesAsTeamA', 'groups.teams.gamesAsTeamB');

        $this->groups = $tournament->groups->map(function ($group) {
            foreach ($group->teams as $team) {
                $played = $team->gamesAsTeamA
                    ->merge($team->gamesAsTeamB)
                    ->filter(fn($game) => $game->status === \App\Enums\GameStatus::Finished);

                $stats = [
                    'match_played' => $played->count(), // ✅ 試合数を追加
                    'win' => 0,
                    'draw' => 0,
                    'lose' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'points' => 0,
                ];

                foreach ($played as $game) {
                    $isA = $game->team_a_id === $team->id;
                    $gf = $isA ? $game->team_a_score : $game->team_b_score;
                    $ga = $isA ? $game->team_b_score : $game->team_a_score;

                    $stats['goals_for'] += $gf;
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

            // ✅ ソートを「勝ち点 → 得失点差 → 得点」の優先順位で
            $group->teams = $group->teams
                ->sortByDesc(fn($t) => $t->stats['goals_for']) // 一番最後に並べ替えるために先に呼ぶ
                ->sortByDesc(fn($t) => $t->stats['diff'])
                ->sortByDesc(fn($t) => $t->stats['points'])
                ->values(); // インデックスを振り直し

            return $group;
        });
    }
}
