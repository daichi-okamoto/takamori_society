<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// ヘルスチェック用
Route::get('/health', fn () => response('ok', 200));

// ルートパス → Filament ダッシュボードへ
Route::get('/', function () {
    // Filament のパス（例: 'admin'）
    $panel = trim(config('filament.path', 'admin'), '/');

    // ダッシュボードに飛ばす（未ログイン時は Filament がログイン画面にリダイレクト）
    return redirect()->to("/{$panel}");
});

Route::get('/diag/r2', function () {
    try {
        $disk = Storage::disk('r2');
        $path = 'diag/_ping_'.now()->format('Ymd_His').'_'.Str::random(6).'.txt';
        $ok = $disk->put($path, 'ping', ['visibility' => 'public', 'ContentType' => 'text/plain']);
        $exists = $disk->exists($path);
        $url = config('filesystems.disks.r2.url')
            ? rtrim(config('filesystems.disks.r2.url'),'/').'/'.$path
            : null;
        return ['put'=>$ok, 'exists'=>$exists, 'path'=>$path, 'url'=>$url];
    } catch (\Throwable $e) {
        \Log::error('[R2 DIAG] failed', ['error'=>$e->getMessage()]);
        return response()->json(['error'=>$e->getMessage()], 500);
    }
});

Route::get('/diag/php', function () {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size'       => ini_get('post_max_size'),
        'sys_temp_dir'        => sys_get_temp_dir(),
        'tmp_is_writable'     => is_writable(sys_get_temp_dir()),
        'livewire_tmp'        => storage_path('framework/livewire-tmp'),
        'livewire_tmp_write'  => is_writable(storage_path('framework/livewire-tmp')),
        'app_url'             => config('app.url'),
        'session_secure'      => config('session.secure'),
    ];
});