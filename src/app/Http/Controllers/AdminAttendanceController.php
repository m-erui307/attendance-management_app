<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
{
    // 日付指定（例: 2026-02-12）
    $date = $request->query('date');

    $targetDate = $date
        ? Carbon::createFromFormat('Y-m-d', $date)
        : Carbon::today();

    $prevDate = $targetDate->copy()->subDay()->format('Y-m-d');
    $nextDate = $targetDate->copy()->addDay()->format('Y-m-d');

    // 全ユーザー取得
    $users = User::all();

    $calendar = [];

    foreach ($users as $user) {

        // その日の勤怠を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $targetDate)
            ->first();

        $calendar[] = [
            'user' => $user,
            'attendance' => $attendance,
        ];
    }

    return view('admin_attendance-list', compact(
        'targetDate',
        'prevDate',
        'nextDate',
        'calendar'
    ));
}

    public function show($userId, $date)
{
    $date = \Carbon\Carbon::parse($date);

    $user = \App\Models\User::findOrFail($userId);

    $attendance = \App\Models\Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->where('work_date', $date)
        ->first();

    return view('admin_attendance-detail', [
        'attendance' => $attendance,
        'user' => $user,
        'date' => $date,
    ]);
}

    public function logout(Request $request)
{
    Auth::guard('admin')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login');
}
}
