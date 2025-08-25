<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者だけ作る（代表者はTeamSeederでチームごとに作る）
        $admin = User::firstOrNew(['email' => 'admin@example.com']);
        $admin->fill([
            'name'  => '管理者',
            'kana'  => 'カンリシャ',
            'phone' => '090-0000-0000',
        ]);
        $admin->password = Hash::make('password');
        $admin->role = 'admin';
        $admin->save();
    }
}
