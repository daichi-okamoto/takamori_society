<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Player;
use App\Models\Team;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::query()->get();
        if ($teams->isEmpty()) {
            // Team が無いと所属付与できないので早期終了（必要なら TeamSeeder を先に実行）
            return;
        }

        $admin = User::where('role', 'admin')->first();

        $counter = 1;

        foreach ($teams as $team) {
            // 扱いやすさのため 10人ずつ作る想定：
            // - 前半 6人：Userを持つ（phoneで紐付け可能）
            // - 後半 4人：Userなし（将来ユーザー登録で phone 照合予定 or 無登録のまま）
            for ($i = 1; $i <= 10; $i++) {
                $baseName = "選手{$counter}";
                $baseKana = "センシュ{$counter}";
                $phone    = sprintf('090-%04d-%04d', random_int(1000, 9999), random_int(1000, 9999));

                $userId   = null;

                if ($i <= 6) {
                    // User ありの選手
                    $user = new User([
                        'name'     => $baseName,  // 表示名は合わせてOK（別物でも良い）
                        'kana'     => $baseKana,
                        'email'    => "player{$counter}@example.com",
                        'phone'    => $phone,
                        'password' => Hash::make('password'),
                    ]);
                    $user->role = 'player'; // ← 明示代入
                    $user->save();

                    $userId = $user->id;
                } else {
                    // User なし選手（将来ユーザー登録で phone 照合してリンク）
                    // phone を入れておくと後で /api/me/link-player でヒットさせやすい
                }

                // Player 実体（必ず name/kana を入れる）
                $player = Player::create([
                    'user_id'       => $userId,          // null も許容
                    'name'          => $baseName,        // ← ここが重要：Player側の氏名
                    'kana'          => $baseKana,        // ← Player側のカナ
                    'phone'         => $phone,           // ← 突合キー候補
                    'address'       => '長野県飯田市',
                    'date_of_birth' => now()->subYears(random_int(15, 30))->subDays(random_int(0, 365)),
                ]);

                // 所属（即時承認）
                $approvedBy = $admin?->id ?? $team->leader_id;

                $team->players()->syncWithoutDetaching([
                    $player->id => [
                        'status'      => 'approved',
                        'joined_at'   => now(),
                        'approved_at' => now(),
                        'approved_by' => $approvedBy,
                    ],
                ]);

                $counter++;
            }
        }
    }
}
