<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'date_of_birth',
        'phone',
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
    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
