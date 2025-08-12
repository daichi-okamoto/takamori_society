<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'uploaded_by',
        'description',

        // ストレージ関連
        'disk',         // 例: r2
        'path',         // R2上のキー (例: galleries/1/20250812_xxx.jpg)
        'mime',
        'size',
        'width',
        'height',
        'visibility',   // public | private
    ];

    protected $casts = [
        'size'  => 'integer',
        'width' => 'integer',
        'height'=> 'integer',
    ];

    // 一覧等で便利に使えるようURLを自動で追加
    protected $appends = ['url'];

    /**
     * URL アクセサ
     * - path/disk がある場合はそちらを優先
     * - 旧データ(image_url)がある場合はフォールバック
     */
    public function getUrlAttribute(): ?string
    {
        if (!empty($this->path)) {
            $disk = $this->disk ?: config('filesystems.default');
            $visibility = $this->visibility ?: config("filesystems.disks.$disk.visibility", 'public');

            // 公開運用 → url()、非公開運用 → temporaryUrl()
            if ($visibility === 'public') {
                return Storage::disk($disk)->url($this->path);
            }

            // 期限付きURL（非公開時）
            return Storage::disk($disk)->temporaryUrl($this->path, now()->addMinutes(60));
        }

        // 旧データの互換
        return $this->image_url ?: null;
    }

    /**
     * 関連
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * スコープ: 大会で絞り込み
     */
    public function scopeForTournament($query, $tournamentId)
    {
        return $query->where('tournament_id', $tournamentId);
    }

    /**
     * モデル削除時にストレージから実体も削除（任意）
     * ※ 誤削除が怖ければコメントアウトしておいてOK
     */
    protected static function booted(): void
    {
        static::deleting(function (Gallery $gallery) {
            if (!empty($gallery->path)) {
                $disk = $gallery->disk ?: config('filesystems.default');
                try {
                    Storage::disk($disk)->delete($gallery->path);
                } catch (\Throwable $e) {
                    // ログに残すなど
                    report($e);
                }
            }
        });
    }
}
