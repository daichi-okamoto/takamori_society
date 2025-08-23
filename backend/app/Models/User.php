<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\FcmToken;
// ↓ 追加
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','kana','email','password','role'];
    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ↓ 追加（まずは無条件で true にして403が消えるか確認）
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function player()
    {
        return $this->hasOne(\App\Models\Player::class);
    }
}
