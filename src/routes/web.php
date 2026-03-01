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
    return redirect()->route('attendance.index');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {

    // 勤怠画面
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->middleware('verified')
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

Route::get('/attendance/list', [AttendanceController::class, 'list'])
    ->name('attendance.list');

Route::get('/attendance/detail/{date}', [AttendanceController::class, 'show'])
    ->name('attendance.show');

Route::put('/attendance/{date}', [AttendanceController::class, 'update'])
    ->name('attendance.update');

Route::prefix('admin')->group(function () {

    // ログイン画面表示
    Route::get('/login', function () {
        return view('admin_login');
    })->middleware('guest:admin')->name('admin.login');

    // 管理者ログイン処理
    Route::post('/login', function (Request $request) {

        $admin = \App\Models\Admin::where('email', $request->email)->first();

        if ($admin && \Hash::check($request->password, $admin->password)) {
            \Auth::guard('admin')->login($admin);
            return redirect()->route('admin.attendance.list');
        }
    })->middleware('guest:admin');

    // ログアウト
    Route::post('/logout', [AdminAttendanceController::class, 'logout'])
        ->middleware('auth:admin')
        ->name('admin.logout');

});


Route::prefix('admin')
    ->middleware('auth:admin')
    ->group(function () {

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

        Route::get('/admin/staff', [AdminStaffController::class, 'index'])
            ->name('admin.staff.list');

        Route::get('/staff/{user}', [AdminStaffController::class, 'show'])
            ->name('admin.staff.show');

        Route::get('/admin/request-list', [AdminRequestController::class, 'index'])
            ->name('admin.request.list');

        Route::put('/admin/request/{id}/approve',
        [AdminRequestController::class, 'approve'])
            ->name('admin.request.approve');

        Route::get('/admin/staff/{user}/attendance/csv', [AdminAttendanceController::class, 'exportCsv'])
            ->name('admin.staff.attendance.csv');

});


Route::middleware('auth')->group(function () {
    // 申請一覧画面
    Route::get('/requests', [RequestController::class, 'index'])->name('request.list');

    // 申請送信
    Route::post('/requests', [RequestController::class, 'store'])->name('request.store');
});
