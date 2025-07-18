<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_url',
        'uploaded_by',
        'description',
    ];

    /**
     * 投稿者（ユーザー）とのリレーション
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}