<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;

class AdminCsrf extends Middleware
{
    protected function getCookieName()
    {
        return env('ADMIN_SESSION_COOKIE', 'admin_session');
    }
}