<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('admin_staff-list', compact('users'));
    }

    public function show(User $user)
{
    // 月取得（例: 2026-02）
    $month = request('month')
        ? Carbon::createFromFormat('Y-m', request('month'))
        : Carbon::now();

    $targetMonth = $month->copy();

    $prevMonth = $month->copy()->subMonth()->format('Y-m');
    $nextMonth = $month->copy()->addMonth()->format('Y-m');

    // 月の最初と最後
    $startOfMonth = $month->copy()->startOfMonth();
    $endOfMonth   = $month->copy()->endOfMonth();

    // その月の勤怠を取得（休憩も読み込み）
    $attendances = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
        ->get()
        ->keyBy(fn($item) => $item->work_date->format('Y-m-d'));

    // カレンダー生成
    $calendar = [];

    for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {

        $calendar[] = [
            'date' => $date->copy(),
            'attendance' => $attendances[$date->format('Y-m-d')] ?? null,
        ];
    }

    return view('admin_attendance-staff', compact(
        'user',
        'targetMonth',
        'prevMonth',
        'nextMonth',
        'calendar'
    ));
}
}
