<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\User;
use App\Models\Player;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $tournament = Tournament::with('groups')->first();
        if (! $tournament) return;

        $teamNumber = 1;

        foreach ($tournament->groups as $group) {
            for ($i = 1; $i <= 5; $i++) {

                // 1) チームごとの代表者ユーザーを作成（再実行OK）
                $email = "leader{$teamNumber}@example.com";
                $leaderUser = User::firstOrNew(['email' => $email]);
                $leaderUser->fill([
                    'name'  => "代表者 {$teamNumber}",
                    'kana'  => "ダイヒョウシャ {$teamNumber}",
                    // phoneはユニーク制約に当たらないよう teamNumber を混ぜる
                    'phone' => sprintf('090-%04d-%04d', $teamNumber, $teamNumber),
                ]);
                if (! $leaderUser->exists || ! $leaderUser->password) {
                    $leaderUser->password = Hash::make('password');
                }
                $leaderUser->role = 'team_leader';
                $leaderUser->save();

                // 2) 代表者の Player を用意（user_id で1:1リンク）
                $leaderPlayer = Player::firstOrCreate(
                    ['user_id' => $leaderUser->id],
                    [
                        'name'    => $leaderUser->name,
                        'kana'    => $leaderUser->kana,
                        'phone'   => $leaderUser->phone,
                        'address' => '長野県飯田市',
                    ]
                );

                // 3) チーム作成（代表者をセット）
                $team = Team::create([
                    'name'      => "チーム{$teamNumber}",
                    'leader_id' => $leaderUser->id,
                ]);

                // 4) 大会と紐付け（グループ割当）
                $tournament->teams()->attach($team->id, [
                    'group_id' => $group->id,
                ]);

                // 5) 代表者を選手として所属（即時承認）
                $team->players()->syncWithoutDetaching([
                    $leaderPlayer->id => [
                        'status'      => 'approved',
                        'joined_at'   => now(),
                        'approved_at' => now(),
                        'approved_by' => $leaderUser->id,
                    ],
                ]);

                $teamNumber++;
            }
        }
    }
}
