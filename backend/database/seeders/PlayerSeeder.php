<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\User;
use App\Models\Team;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();
        $counter = 1;

        foreach ($teams as $team) {
            for ($i = 1; $i <= 10; $i++) {
                $user = User::create([
                    'name' => "選手{$counter}",
                    'kana' => "センシュ{$counter}",
                    'email' => "player{$counter}@example.com",
                    'phone' => '090-' . rand(1000,9999) . '-' . rand(1000,9999),
                    'password' => bcrypt('password'),
                    'role' => 'player',
                ]);

                $player = Player::create([
                    'user_id' => $user->id,
                    'address' => '長野県飯田市',
                    'date_of_birth' => now()->subYears(rand(15, 30))->subDays(rand(0, 365)),
                    'phone' => $user->phone,
                ]);

                $player->teams()->attach($team->id, ['joined_at' => now()]);
                $counter++;
            }
        }
    }
}
