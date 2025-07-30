<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = ['name', 'date', 'location'];

    // 試合（Game）とのリレーション
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    // グループとのリレーション
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function teams()
    {
        return $this->hasManyThrough(
            Team::class,
            Group::class,
            'tournament_id', // groups テーブルの外部キー
            'group_id',      // teams テーブルの外部キー
            'id',            // tournaments テーブルの主キー
            'id'             // groups テーブルの主キー
        );
    }
}
