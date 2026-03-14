<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AdminLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('guest')->group(function () {
    // ログイン
    Route::get('/login', fn() => view('auth.login'))->name('login');

    // 会員登録
    Route::get('/register', fn() => view('auth.register'))->name('register');
});

Route::middleware(['web', 'auth:web'])->group(function () {
    // ログアウト
    Route::post('/logout', function () {
            Auth::guard('web')->logout();
            return redirect('/login');
        })->name('logout');

    // 勤怠画面（メール認証必須）
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->middleware('verified')
        ->name('attendance.index');

    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    Route::post('/break/start', [BreakController::class, 'start'])->name('break.start');
    Route::post('/break/end', [BreakController::class, 'end'])->name('break.end');

    // 申請一覧・送信
    Route::get('/requests', [RequestController::class, 'index'])->name('request.list');
    Route::post('/requests', [RequestController::class, 'store'])->name('request.store');

    // 勤怠詳細・更新
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{date}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{date}', [AttendanceController::class, 'update'])->name('attendance.update');

    // メール認証関連
    Route::get('/email/verify', fn() => view('auth.verify-email'))
        ->middleware('auth')->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('attendance.index');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '認証メールを再送しました。');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
});


Route::middleware('guest:admin')->group(function () {

    // 管理者ログイン
    Route::get('/admin/login', [AdminLoginController::class,'showLoginForm'])->name('admin.login');

    Route::post('/admin/login', [AdminLoginController::class,'login'])->name('admin.login.post');
});


Route::prefix('admin')
    ->middleware('auth:admin')
    ->group(function () {

        Route::post('/logout', function () {
            Auth::guard('admin')->logout();
            return redirect('/admin/login');
        })->name('admin.logout');

        Route::get('/attendance', [AdminAttendanceController::class, 'index'])
            ->name('admin.attendance.list');

        Route::get('/attendance/{user}/{date}',
            [AdminAttendanceController::class, 'show'])
            ->name('admin.attendance.show');

        Route::put('/attendance/{user}/{date}',
            [AdminAttendanceController::class, 'update'])
            ->name('admin.attendance.update');

        Route::get('/request/{id}',
            [AdminRequestController::class, 'show'])
            ->name('admin.request.show');

        Route::get('/staff', [AdminStaffController::class, 'index'])
            ->name('admin.staff.list');

        Route::get('/staff/{user}', [AdminStaffController::class, 'show'])
            ->name('admin.staff.show');

        Route::get('/request-list', [AdminRequestController::class, 'index'])
            ->name('admin.request.list');

        Route::put('/request/{id}/approve',
        [AdminRequestController::class, 'approve'])
            ->name('admin.request.approve');

        Route::get('/staff/{user}/attendance/csv', [AdminAttendanceController::class, 'exportCsv'])
            ->name('admin.staff.attendance.csv');

});
