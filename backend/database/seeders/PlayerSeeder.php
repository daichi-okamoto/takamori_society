<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\Team;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            for ($i = 1; $i <= 5; $i++) {
                // 選手を作成
                $player = Player::create([
                    'name' => "選手{$team->id}-{$i}",
                    'address' => '長野県飯田市',
                    'date_of_birth' => now()->subYears(rand(15, 25))->subDays(rand(0, 365)),
                ]);

                // 中間テーブルに所属情報を登録
                $player->teams()->attach($team->id, [
                    'joined_at' => now(),
                ]);
            }
        }
    }
}
