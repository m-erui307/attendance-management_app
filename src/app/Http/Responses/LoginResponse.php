<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->is('admin/*')) {
            return redirect()->intended('/admin/attendance');
        }

        return redirect()->intended('/attendance');
    }
}