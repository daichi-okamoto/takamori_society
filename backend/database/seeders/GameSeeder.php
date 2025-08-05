<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Group;
use App\Enums\GameStatus;
use Illuminate\Support\Carbon;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $groups = Group::with('teams')->get();

        foreach ($groups as $group) {
            $teams = $group->teams;

            // 総当たり（各チームが全チームと1回ずつ対戦）
            for ($i = 0; $i < count($teams); $i++) {
                for ($j = $i + 1; $j < count($teams); $j++) {
                    $teamA = $teams[$i];
                    $teamB = $teams[$j];

                    Game::create([
                        'tournament_id' => $group->tournament_id,
                        'group_id' => $group->id,
                        'game_date' => Carbon::now()->addDays(rand(1, 10))->setTime(rand(9, 18), 0),
                        'team_a_id' => $teamA->id,
                        'team_b_id' => $teamB->id,
                        'team_a_score' => rand(0, 5),
                        'team_b_score' => rand(0, 5),
                        'status' => GameStatus::Finished,
                        'stage' => 'group',
                    ]);
                }
            }
        }
    }
}
