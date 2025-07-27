<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Team;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::pluck('id')->toArray();

        for ($i = 1; $i <= 10; $i++) {
            // ランダムに異なる2チームを選出
            $teamA = $teams[array_rand($teams)];
            do {
                $teamB = $teams[array_rand($teams)];
            } while ($teamA === $teamB);

            Game::create([
                'group_id' => rand(1, 3),
                'date' => now()->addDays($i),
                'time' => now()->setTime(rand(9, 18), 0),
                'place' => '高森グラウンド',
                'team_a_id' => $teamA,
                'team_b_id' => $teamB,
                'team_a_score' => rand(0, 5),
                'team_b_score' => rand(0, 5),
                'status' => '終了',
            ]);
        }
    }
}
