<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teamNumber = 1;

        foreach ([1, 2, 3] as $groupId) {
            for ($i = 1; $i <= 5; $i++) {
                Team::create([
                    'group_id' => $groupId,
                    'name' => "チーム{$teamNumber}",
                    'leader_id' => null, // 必要に応じてユーザーIDをセット
                ]);
                $teamNumber++;
            }
        }
    }
}
