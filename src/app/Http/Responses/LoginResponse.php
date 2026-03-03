<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->input('role') === 'admin') {
            // 管理者ログイン後
            return redirect()->route('admin.attendance.list');
        }

        // ユーザーログイン後
        return redirect()->route('attendance.index');
    }
}