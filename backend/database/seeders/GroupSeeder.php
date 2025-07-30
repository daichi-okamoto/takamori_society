<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['A', 'B', 'C'] as $name) {
            Group::create([
                'tournament_id' => 1,
                'name' => "グループ{$name}",
            ]);
        }
    }
}
