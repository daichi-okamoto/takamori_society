<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    */
    'class_namespace' => 'App\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    */
    'view_path' => resource_path('views/livewire'),

    /*
    |--------------------------------------------------------------------------
    | Layout (未使用なら null のままでOK)
    |--------------------------------------------------------------------------
    */
    'layout' => null,

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    | Livewire の一時アップロード先を R2 / livewire-tmp に固定
    */
    'temporary_file_upload' => [
        // R2 を使う
        'disk' => env('LIVEWIRE_UPLOAD_DISK', 'r2'),

        // 一時ディレクトリ（コード側の想定と合わせる）
        'directory' => env('LIVEWIRE_UPLOAD_DIR', 'livewire-tmp'),

        // そのほかは必要に応じて
        'rules' => null,
        'middleware' => 'throttle:60,1',
        'preview_mimes' => [
            'png','gif','bmp','svg','wav','mp4',
            'mov','avi','wmv','mp3','m4a',
            'jpg','jpeg','mpga','webp','wma',
        ],
        'max_upload_time' => 10, // 分
        'cleanup' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | その他の推奨デフォルト
    |--------------------------------------------------------------------------
    */
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],
    'inject_morph_markers' => true,
    'pagination_theme' => 'tailwind',
];
