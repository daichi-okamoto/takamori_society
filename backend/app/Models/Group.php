<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tournament_id', // ← 追加
    ];

    /**
     * 所属するトーナメント
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    /**
     * 所属チームとのリレーション
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }
}
