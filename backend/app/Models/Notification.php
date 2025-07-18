<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',    // nullなら全体通知
        'title',
        'message',
        'sent_at',
    ];

    /**
     * 通知対象チーム
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}