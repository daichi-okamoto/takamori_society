<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $tournament = Tournament::first();

        if (! $tournament) {
            return;
        }

        $teamNumber = 1;
        foreach ($tournament->groups as $group) {
            for ($i = 1; $i <= 5; $i++) {
                $team = Team::create([
                    'name' => "チーム{$teamNumber}",
                    'leader_id' => 2, // 仮の代表者ID
                ]);

                // pivot に登録（大会 + チーム + グループ）
                $tournament->teams()->attach($team->id, [
                    'group_id' => $group->id,
                ]);

                $teamNumber++;
            }
        }
    }
}
