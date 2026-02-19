<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
{
    if (Auth::guard('admin')->check()) {
        return redirect()->intended('/admin/attendance');
    }

    if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            // メール認証が済んでいない場合はメール認証画面へ
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // 認証済みなら通常勤怠画面へ
            return redirect()->intended('/attendance');
        }
}
}