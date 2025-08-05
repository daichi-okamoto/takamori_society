<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Game;
use App\Services\FcmService;
use Illuminate\Support\Facades\DB;

class GameNotificationController extends Controller
{
    public function send(Request $request, Game $game, FcmService $fcmService)
    {
        // 試合に参加する両チームのIDを取得
        $teamIds = [$game->team_a_id, $game->team_b_id];

        foreach ($teamIds as $teamId) {
            if (!$teamId) continue;

            // チーム所属選手のユーザーを取得
            $players = DB::table('player_team')
                ->join('players', 'player_team.player_id', '=', 'players.id')
                ->join('users', 'players.user_id', '=', 'users.id')
                ->where('player_team.team_id', $teamId)
                ->whereNull('player_team.left_at') // 退団していない選手
                ->select('users.id')
                ->get();

            foreach ($players as $player) {
                $user = \App\Models\User::find($player->id);

                if (!$user) continue;

                foreach ($user->fcmTokens as $token) {
                    $fcmService->send(
                        $token->token,
                        '試合通知',
                        "{$game->game_date->format('Y-m-d H:i')} の試合が予定されています。"
                    );
                }
            }
        }

        return response()->json(['message' => '通知を送信しました']);
    }
}
