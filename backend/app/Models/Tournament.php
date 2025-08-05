<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    // チームとの多対多リレーション（pivot に group_id を含む）
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'tournament_team')
                    ->withPivot('group_id')
                    ->withTimestamps();
    }
}
