<?php

use Illuminate\Support\Facades\Route;

// ヘルスチェック用
Route::get('/health', fn () => response('ok', 200));

// ルートパス → Filament ダッシュボードへ
Route::get('/', function () {
    // Filament のパス（例: 'admin'）
    $panel = trim(config('filament.path', 'admin'), '/');

    // ダッシュボードに飛ばす（未ログイン時は Filament がログイン画面にリダイレクト）
    return redirect()->to("/{$panel}");
});
