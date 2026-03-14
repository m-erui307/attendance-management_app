<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Http\Responses\RegisterResponse;
use App\Http\Requests\AdminLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);

        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // 認証処理
        Fortify::authenticateUsing(function (Request $request) {

            // 管理者ログイン判定
            if ($request->is('admin/*') || $request->is('admin/login')) {
                /** @var AdminLoginRequest $adminRequest */
                $adminRequest = app(AdminLoginRequest::class)->merge($request->all());
                $adminRequest->validateResolved();

                $admin = Admin::where('email', $adminRequest->email)->first();

                if ($admin && Hash::check($adminRequest->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    return $admin;
                }

                return null;
            }

            // ユーザーログイン
            /** @var LoginRequest $userRequest */
            $userRequest = app(LoginRequest::class)->merge($request->all());
            $userRequest->validateResolved();

            $user = User::where('email', $userRequest->email)->first();

            if ($user && Hash::check($userRequest->password, $user->password)) {
                return $user;
            }

            return null;
        });
    }
}
