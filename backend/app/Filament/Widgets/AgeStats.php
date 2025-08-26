<?php

namespace App\Filament\Widgets;

use App\Models\Player;
use App\Models\Team;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgeStats extends BaseWidget
{
    // デフォルト選択（全体）
    public ?string $filter = 'all';   // ← 追加

    protected ?string $heading = '選手の年齢サマリー';

    public function getFilters(): ?array
    {
        return ['all' => '全体'] + Team::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    protected function getStats(): array
    {
        $teamId = $this->filter && $this->filter !== 'all' ? (int) $this->filter : null;

        $q = Player::query()->whereNotNull('date_of_birth');
        if ($teamId) {
            $q->whereHas('teams', fn ($t) => $t->where('teams.id', $teamId));
        }

        $ages = $q->pluck('date_of_birth')
            ->map(fn ($dob) => Carbon::parse($dob)->age)
            ->filter(fn ($age) => $age >= 0)
            ->values();

        $count = $ages->count();
        $titleSuffix = $teamId ? '（チーム別）' : '（全体）';

        if ($count === 0) {
            return [
                Stat::make('平均年齢' . $titleSuffix, '—')->description('対象選手がいません'),
                Stat::make('最年少', '—'),
                Stat::make('最年長', '—'),
            ];
        }

        $avg = round($ages->avg(), 1);
        $min = $ages->min();
        $max = $ages->max();

        return [
            Stat::make('平均年齢' . $titleSuffix, "{$avg} 歳")->description("対象選手 {$count} 名"),
            Stat::make('最年少', "{$min} 歳"),
            Stat::make('最年長', "{$max} 歳"),
        ];
    }
}
