<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * 選手一覧取得
     */
    public function index()
    {
        return response()->json(
            Player::with('teams')->get()
        );
    }

    /**
     * 選手登録
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
        ]);

        $player = Player::create([
            'team_id' => $request->team_id,
            'name' => $request->name,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return response()->json($player, 201);
    }

    /**
     * 特定選手の詳細取得
     */
    public function show(string $id)
    {
        $player = Player::with('team')->findOrFail($id);

        return response()->json($player);
    }

    /**
     * 選手情報更新
     */
    public function update(Request $request, string $id)
    {
        $player = Player::findOrFail($id);

        $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
        ]);

        $player->update($request->only(['team_id', 'name', 'address', 'date_of_birth']));

        return response()->json($player);
    }

    /**
     * 選手削除
     */
    public function destroy(string $id)
    {
        $player = Player::findOrFail($id);
        $player->delete();

        return response()->json(['message' => '選手を削除しました']);
    }
}