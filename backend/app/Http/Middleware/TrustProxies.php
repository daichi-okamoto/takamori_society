<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    // すべてのプロキシを信頼（Koyeb エッジ→アプリ間のプロキシを想定）
    protected $proxies = '*';

    // X-Forwarded-* を使ってスキーム判定（AWS ELB 互換も含めておくと安全）
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
