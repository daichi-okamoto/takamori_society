<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teamNumber = 1;
        foreach (range(1, 3) as $groupId) {
            for ($i = 1; $i <= 5; $i++) {
                Team::create([
                    'name' => "チーム{$teamNumber}",
                    'group_id' => $groupId,
                    'leader_id' => 2, // 上で作った代表者を仮割り当て
                ]);
                $teamNumber++;
            }
        }
    }
}
