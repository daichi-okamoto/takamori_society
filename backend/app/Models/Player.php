<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kana',
        'address',
        'date_of_birth',
    ];

    /**
     * チームとの多対多リレーション（中間テーブル）
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class)
                    ->withPivot('joined_at', 'left_at')
                    ->withTimestamps();
    }
}
