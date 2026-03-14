<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    // 管理者ログイン画面表示
    public function showLoginForm(Request $request)
    {
        $request->session()->regenerateToken();

        return view('admin_login');
    }

    // ログイン処理
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email','password');

        if (Auth::guard('admin')->attempt($credentials)) {

            $request->session()->regenerate();

            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません'
        ])->onlyInput('email');

    }

    // ログアウト
    public function logout()
    {
        // 管理者ログアウト
        Auth::guard('admin')->logout();

        // Admin 用セッション cookie を削除
        cookie()->queue(cookie()->forget(env('ADMIN_SESSION_COOKIE', 'admin_session')));

        // Admin 用 CSRF cookie を削除
        cookie()->queue(cookie()->forget('XSRF-TOKEN')); // Admin 用 CSRF

        return redirect()->route('admin.login');
    }
}
