<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'match_played',
        'win',
        'lose',
        'draw',
        'goals_for',
        'goals_against',
    ];

    /**
     * チームとのリレーション
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}