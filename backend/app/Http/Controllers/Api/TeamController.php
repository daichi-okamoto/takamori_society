<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        return Team::with('group')->get();
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'group_id' => 'nullable|exists:groups,id',
                'leader_id' => 'nullable|exists:users,id',
            ]);

            $team = Team::create($request->only(['name', 'group_id', 'leader_id']));

            return response()->json($team, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group_id' => 'nullable|exists:groups,id',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        $team->update($request->only(['name', 'group_id', 'leader_id']));

        return response()->json($team);
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(['message' => '削除しました']);
    }
}