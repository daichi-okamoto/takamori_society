<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\GameStatus;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',  // ← 追加
        'date',
        'time',
        'place',
        'team_a_id',
        'team_b_id',
        'team_a_score',
        'team_b_score',
        'status',
    ];

    protected $casts = [
        'status' => GameStatus::class,
    ];

    /**
     * 大会とのリレーション
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * チームAとのリレーション
     */
    public function teamA()
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    /**
     * チームBとのリレーション
     */
    public function teamB()
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function getMatchCardAttribute(): string
    {
        return ($this->teamA?->name ?? '未定') . ' vs ' . ($this->teamB?->name ?? '未定');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function getMatchResultAttribute(): string
    {
        if ($this->status === \App\Enums\GameStatus::Finished) {
            return ($this->team_a_score ?? '-') . ' - ' . ($this->team_b_score ?? '-');
        }
        return '未試合';
    }
}
