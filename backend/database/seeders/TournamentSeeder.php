<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;
use App\Models\Team;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        // 大会を1つ作成
        $tournament = Tournament::create([
            'name' => '高森トーナメント2025',
            'date' => now()->addWeek(),
            'location' => '山吹ほたるパークグラウンド',
        ]);

        // 大会内に3つのグループを作成
        foreach (['A', 'B', 'C'] as $name) {
            $tournament->groups()->create([
                'name' => "グループ{$name}",
            ]);
        }
    }
}
