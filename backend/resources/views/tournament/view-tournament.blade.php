<x-filament::page>
    <h1 class="text-2xl font-bold mb-6">{{ $record->name }} 詳細</h1>
    <p class="mb-4">開催日: {{ $record->date }} / 会場: {{ $record->location }}</p>

    @foreach ($groups as $group)
    <div class="mb-8">
        <h2 class="text-xl font-semibold mt-6 mb-3">{{ $group->name }} グループ</h2>

        <!-- 順位表 -->
        <div class="overflow-x-auto">
            <table class="table-auto min-w-max border border-gray-300 mb-6">
                <thead>
                    <tr>
                        <th class="border px-4 py-2 whitespace-nowrap">順位</th>
                        <th class="border px-4 py-2 whitespace-nowrap">チーム名</th>
                        <th class="border px-4 py-2 whitespace-nowrap">試合数</th>
                        <th class="border px-4 py-2 whitespace-nowrap">勝ち点</th>
                        <th class="border px-4 py-2 whitespace-nowrap">勝</th>
                        <th class="border px-4 py-2 whitespace-nowrap">分</th>
                        <th class="border px-4 py-2 whitespace-nowrap">負</th>
                        <th class="border px-4 py-2 whitespace-nowrap">得点</th>
                        <th class="border px-4 py-2 whitespace-nowrap">失点</th>
                        <th class="border px-4 py-2 whitespace-nowrap">得失点</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group->teams as $index => $team)
                        <tr>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->name }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['match_played'] }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['points'] }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['win'] }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['draw'] }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['lose'] }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['goals_for'] }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['goals_against'] }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $team->stats['diff'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 試合表 -->
        <h4 class="text-md font-bold mt-6 mb-2">試合結果</h4>
        <div class="overflow-x-auto">
            <table class="table-auto min-w-max border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="border px-4 py-2 whitespace-nowrap">日時</th>
                        <th class="border px-4 py-2 whitespace-nowrap">カード</th>
                        <th class="border px-4 py-2 whitespace-nowrap">スコア</th>
                        <th class="border px-4 py-2 whitespace-nowrap">ステージ</th>
                        <th class="border px-4 py-2 whitespace-nowrap">通知</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group->games as $game)
                        <tr>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $game->game_date->format('Y-m-d H:i') }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $game->match_card }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ $game->match_result }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">{{ ucfirst($game->stage) }}</td>
                            <td class="border px-4 py-2 whitespace-nowrap">
                                <form action="{{ route('games.notify', $game->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded">
                                        通知を送信
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</x-filament::page>
