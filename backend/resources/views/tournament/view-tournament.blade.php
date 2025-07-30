<x-filament::page>
    <h1 class="text-2xl font-bold mb-6">{{ $record->name }} 詳細</h1>
    <p class="mb-4">開催日: {{ $record->date }} / 会場: {{ $record->location }}</p>

    @foreach ($groups as $group)
        <h2 class="text-xl font-semibold mt-6 mb-3">{{ $group->name }} グループ</h2>
        <table class="table-auto w-full border border-gray-300 mb-6">
            <thead>
                <tr class="bg-black-100">
                    <th class="border px-4 py-2">順位</th>
                    <th class="border px-4 py-2">チーム名</th>
                    <th class="border px-4 py-2">試合数</th> {{-- ✅ 追加 --}}
                    <th class="border px-4 py-2">勝ち点</th>
                    <th class="border px-4 py-2">勝</th>
                    <th class="border px-4 py-2">分</th>
                    <th class="border px-4 py-2">負</th>
                    <th class="border px-4 py-2">得点</th>
                    <th class="border px-4 py-2">失点</th>
                    <th class="border px-4 py-2">得失点</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group->teams as $index => $team)
                    <tr>
                        <td class="border px-4 py-2">{{ $index + 1 }}</td>
                        <td class="border px-4 py-2">{{ $team->name }}</td>
                        <td class="border px-4 py-2">{{ $team->stats['match_played'] }}</td> {{-- ✅ 追加 --}}
                        <td class="border px-4 py-2">{{ $team->stats['points'] }}</td>
                        <td class="border px-4 py-2">{{ $team->stats['win'] }}</td>
                        <td class="border px-4 py-2">{{ $team->stats['draw'] }}</td>
                        <td class="border px-4 py-2">{{ $team->stats['lose'] }}</td>
                        <td class="border px-4 py-2">{{ $team->stats['goals_for'] }}</td>
                        <td class="border px-4 py-2">{{ $team->stats['goals_against'] }}</td>
                        <td class="border px-4 py-2">{{ $team->stats['diff'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</x-filament::page>
