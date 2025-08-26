<?php

namespace App\Filament\Widgets;

use App\Models\Player;
use App\Models\Team;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AgeDistributionChart extends ChartWidget
{
    public ?string $filter = 'all';   // ← 追加

    protected static ?string $heading = '年齢分布';
    protected static ?string $maxHeight = '300px';

    public function getFilters(): ?array
    {
        return ['all' => '全体'] + Team::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
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

        if ($ages->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [[ 'label' => '人数', 'data' => [] ]],
            ];
        }

        // 5歳刻み
        $min = max(floor($ages->min() / 5) * 5, 0);
        $max = ceil($ages->max() / 5) * 5;
        $bins = collect(range($min, $max, 5));

        $labels = $bins->map(fn ($lo) => "{$lo}–" . ($lo + 4))->all();
        $counts = $bins->map(function (int $lo) use ($ages) {
            $hi = $lo + 4;
            return $ages->filter(fn ($a) => $a >= $lo && $a <= $hi)->count();
        })->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $teamId ? '人数（チーム）' : '人数（全体）',
                    'data' => $counts,
                ],
            ],
        ];
    }
}
