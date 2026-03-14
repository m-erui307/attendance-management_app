<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Session\Store;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\Middleware\StartSession;


class AdminSession extends StartSession
{
    /**
     * Admin 用のセッション設定を返す
     */
    protected function getSessionConfig($request)
    {
        return [
            'driver' => 'file',
            'files' => storage_path('framework/sessions/admin'),
            'lifetime' => env('ADMIN_SESSION_LIFETIME', 120),
            'expire_on_close' => false,
            'encrypt' => false,
            'cookie' => env('ADMIN_SESSION_COOKIE', 'admin_session'),
            'path' => '/',
            'domain' => null,
            'secure' => env('SESSION_SECURE_COOKIE', false),
            'http_only' => true,
            'same_site' => 'lax',
        ];
    }

    /**
     * セッション名を明示的に上書き
     */
    public function getSessionName($request)
    {
        return env('ADMIN_SESSION_COOKIE', 'admin_session');
    }
}
