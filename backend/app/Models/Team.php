<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Game;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'leader_id',
    ];

    /**
     * このチームが所属するグループ（トーナメント内のグループ）
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class)
            ->withPivot(['status', 'requested_at', 'approved_at', 'approved_by', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function ranking()
    {
        return $this->hasOne(Ranking::class);
    }

    /**
     * モデル削除時の処理：player_teamのdetach、future games削除
     */
    protected static function booted()
    {
        static::deleting(function (Team $team) {
            $today = now()->toDateString();

            // 未来の試合は削除（従来どおり）
            Game::where(function ($query) use ($team) {
                    $query->where('team_a_id', $team->id)
                        ->orWhere('team_b_id', $team->id);
                })
                ->where('date', '>', $today)
                ->delete();

            // 過去の試合にバックアップ名を保存
            Game::where('team_a_id', $team->id)
                ->where('date', '<=', $today)
                ->update(['team_a_name_backup' => $team->name]);

            Game::where('team_b_id', $team->id)
                ->where('date', '<=', $today)
                ->update(['team_b_name_backup' => $team->name]);

            // 中間テーブルをdetach
            $team->players()->detach();
        });
    }

    public function gamesAsTeamA()
    {
        return $this->hasMany(Game::class, 'team_a_id');
    }

    public function gamesAsTeamB()
    {
        return $this->hasMany(Game::class, 'team_b_id');
    }

    // トーナメントチームの中間テーブル
    public function tournaments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_team')
                    ->withPivot('group_id')
                    ->withTimestamps();
    }
}