<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * 所属チームとのリレーション
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * グループ内の試合とのリレーション
     */
    public function matches()
    {
        return $this->hasMany(Match::class);
    }
}