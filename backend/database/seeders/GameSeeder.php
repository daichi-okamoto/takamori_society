<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Group;
use App\Enums\GameStatus;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $groups = Group::with('teams')->get();

        foreach ($groups as $group) {
            $teamIds = $group->teams->pluck('id')->toArray();

            // チームが2つ以上ある場合のみ試合を作成
            if (count($teamIds) < 2) {
                continue;
            }

            for ($i = 1; $i <= 10; $i++) {
                $teamA = $teamIds[array_rand($teamIds)];
                do {
                    $teamB = $teamIds[array_rand($teamIds)];
                } while ($teamA === $teamB);

                Game::create([
                    'tournament_id' => $group->tournament_id,
                    'group_id' => $group->id,
                    'date' => now()->addDays($i),
                    'time' => now()->setTime(rand(9, 18), 0),
                    'place' => '山吹ほたるパークグラウンド',
                    'team_a_id' => $teamA,
                    'team_b_id' => $teamB,
                    'team_a_score' => rand(0, 5),
                    'team_b_score' => rand(0, 5),
                    'status' => GameStatus::Finished,
                ]);
            }
        }
    }
}
