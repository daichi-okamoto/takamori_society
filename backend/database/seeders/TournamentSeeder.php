<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        Tournament::create([
            'name' => '高森トーナメント2025',
            'date' => now()->addWeek(),
            'location' => '山吹ほたるパークグラウンド',
        ]);
    }
}
