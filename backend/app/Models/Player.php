<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'kana',
        'phone',
        'address',
        'date_of_birth',
    ];

    /**
     * チームとの多対多リレーション（中間テーブル）
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->withPivot(['status', 'requested_at', 'approved_at', 'approved_by', 'joined_at', 'left_at'])
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
