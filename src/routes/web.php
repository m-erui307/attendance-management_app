<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\UserController;

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


// ビュー確認用
Route::get('/', [UserController::class, 'index']);


// ログイン
Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

// 会員登録
Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register');

// メール認証案内画面
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール認証完了（リンククリック時）
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->intended();
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 勤怠画面（ログイン＋メール認証必須）
Route::get('/attendance', function () {
    return view('attendance');
})->middleware(['auth', 'verified'])->name('attendance');

// ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {

    // 勤怠画面
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 出勤
    Route::post('/attendance/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');

    // 退勤
    Route::post('/attendance/end', [AttendanceController::class, 'end'])
        ->name('attendance.end');

    // 休憩開始
    Route::post('/break/start', [BreakController::class, 'start'])
        ->name('break.start');

    // 休憩終了
    Route::post('/break/end', [BreakController::class, 'end'])
        ->name('break.end');
});