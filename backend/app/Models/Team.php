<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'leader_id',
    ];

    /**
     * グループとのリレーション
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * チームリーダー（usersテーブル）とのリレーション
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * 選手一覧（playersテーブル）とのリレーション
     */
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    /**
     * このチームの順位情報
     */
    public function ranking()
    {
        return $this->hasOne(Ranking::class);
    }
}