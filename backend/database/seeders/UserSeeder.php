<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者
        User::create([
            'name' => '管理者',
            'kana' => 'カンリシャ',
            'email' => 'admin@example.com',
            'phone' => '090-0000-0000',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // チーム代表（仮）
        User::create([
            'name' => '代表者 太郎',
            'kana' => 'ダイヒョウシャ タロウ',
            'email' => 'leader@example.com',
            'phone' => '090-1111-1111',
            'password' => bcrypt('password'),
            'role' => 'team_leader',
        ]);
    }
}
