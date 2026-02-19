<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
  {
    // 月の日付一覧
$days = [];
$start = $targetMonth->copy();
$end = $targetMonth->copy()->endOfMonth();
while ($start <= $end) {
    $days[] = $start->copy();
    $start->addDay();
}

// 勤怠データ取得
$attendances = Attendance::with('breaks')
    ->where('user_id', auth()->id())
    ->whereYear('work_date', $targetMonth->year)
    ->whereMonth('work_date', $targetMonth->month)
    ->get()
    ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));

// カレンダー用配列作成
$calendar = [];
foreach ($days as $day) {
    $dayKey = $day->format('Y-m-d');
    $attendance = $attendances->get($dayKey);

    if ($attendance) {
        $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
        $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

        $breakMinutes = $attendance->breaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end) return 0;
            return Carbon::parse($break->break_start)
                ->diffInMinutes(Carbon::parse($break->break_end));
        });

        $workMinutes = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) - $breakMinutes : 0;

        $attendance->clock_in = $clockIn;
        $attendance->clock_out = $clockOut;
        $attendance->break_time = sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        $attendance->total_time = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
    }

    $calendar[] = [
        'date' => $day,
        'attendance' => $attendance,
    ];

    return view('admin_attendance-list', compact(
    'calendar',
    'targetMonth',
    'prevMonth',
    'nextMonth'
));
}
  }
}