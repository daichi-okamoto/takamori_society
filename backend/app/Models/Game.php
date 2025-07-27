<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'date',
        'time',
        'place',
        'team_a_id',
        'team_b_id',
        'team_a_score',
        'team_b_score',
        'status',
    ];

    /**
     * グループとのリレーション
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
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
}