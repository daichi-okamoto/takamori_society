<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        Group::create(['name' => 'グループA']);
        Group::create(['name' => 'グループB']);
        Group::create(['name' => 'グループC']);
    }
}
