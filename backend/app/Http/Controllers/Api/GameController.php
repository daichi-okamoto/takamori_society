<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * 試合一覧取得
     */
    public function index()
    {
        return response()->json(
            Game::with(['group', 'teamA', 'teamB'])->get()
        );
    }

    /**
     * 試合登録
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'nullable|exists:groups,id',
            'date' => 'required|date',
            'time' => 'required',
            'place' => 'required|string|max:255',
            'team_a_id' => 'required|exists:teams,id',
            'team_b_id' => 'required|exists:teams,id',
            'team_a_score' => 'nullable|integer',
            'team_b_score' => 'nullable|integer',
            'status' => 'required|string|max:50',
        ]);

        $game = Game::create($request->all());

        return response()->json($game, 201);
    }

    /**
     * 試合詳細取得
     */
    public function show($id)
    {
        $game = Game::with(['group', 'teamA', 'teamB'])->findOrFail($id);
        return response()->json($game);
    }

    /**
     * 試合更新
     */
    public function update(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        $request->validate([
            'group_id' => 'nullable|exists:groups,id',
            'date' => 'nullable|date',
            'time' => 'nullable',
            'place' => 'nullable|string|max:255',
            'team_a_id' => 'nullable|exists:teams,id',
            'team_b_id' => 'nullable|exists:teams,id',
            'team_a_score' => 'nullable|integer',
            'team_b_score' => 'nullable|integer',
            'status' => 'nullable|string|max:50',
        ]);

        $game->update($request->all());

        return response()->json($game);
    }

    /**
     * 試合削除
     */
    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        $game->delete();

        return response()->json(['message' => '試合を削除しました']);
    }
}
