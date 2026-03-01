<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * 勤怠画面表示
     */
    public function index()
    {
        $attendance = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->where('work_date', today())
            ->first();

        return view('attendance', compact('attendance'));
    }

    /**
     * 出勤
     */
    public function start()
    {
        Attendance::create([
            'user_id'   => auth()->id(),
            'work_date' => today(),
            'clock_in'  => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 退勤
     */
    public function end()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->firstOrFail();

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 勤怠一覧
     */
    public function list(Request $request)
    {
    // 表示する月
    $targetMonth = $request->month
        ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
        : Carbon::now()->startOfMonth();

    $prevMonth = $targetMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

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
        ->keyBy(fn ($a) => $a->work_date->format('Y-m-d')); // 文字列キーに統一

    // カレンダー用配列作成
    $calendar = [];
    foreach ($days as $day) {
        $dayKey = $day->format('Y-m-d');
        $attendance = $attendances->get($dayKey);

        if ($attendance) {
            // clock_in / clock_out を Carbon に変換（nullチェック）
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

            // 休憩時間（分）
            $breakMinutes = $attendance->breaks->sum(function ($break) {
                if (!$break->break_start || !$break->break_end) return 0;
                return Carbon::parse($break->break_start)
                    ->diffInMinutes(Carbon::parse($break->break_end));
            });

            // 勤務時間（分）
            $workMinutes = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) - $breakMinutes : 0;

            // 表示用
            $attendance->clock_in = $clockIn;
            $attendance->clock_out = $clockOut;
            $attendance->break_time = sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
            $attendance->total_time = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
        }

        $calendar[] = [
            'date' => $day,
            'attendance' => $attendance,
        ];
    }

    return view('attendance-list', compact(
        'calendar',
        'targetMonth',
        'prevMonth',
        'nextMonth'
    ));
}

    /**
    * 勤怠詳細
    */
    public function show($date)
{
    $date = Carbon::parse($date);
    $user = auth()->user();

    // 通常の勤怠
    $attendance = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->where('work_date', $date)
        ->first();

    // その日の最新申請
    $attendanceRequest = \App\Models\AttendanceRequest::where('user_id', $user->id)
        ->where('target_date', $date)
        ->latest()
        ->first();

    // 申請があれば優先表示
    if ($attendanceRequest) {

        $breaks = collect($attendanceRequest->breaks ?? [])
    ->filter(function ($break) {
        return !empty($break['start']) || !empty($break['end']);
    })
    ->map(function ($break) {
        return [
            'break_start' => $break['start'] ?? null,
            'break_end'   => $break['end'] ?? null,
        ];
    })
    ->values()
    ->toArray();

        return view('attendance-detail', [
            'attendance' => $attendanceRequest,
            'user' => $user,
            'date' => $date,
            'breaks' => $breaks,
            'pendingRequest' => $attendanceRequest->status === 'pending',
        ]);
    }

    // 申請がなければ通常勤怠
    $pendingRequest = false;

    $breaks = $attendance
        ? $attendance->breaks->map(function ($break) {
            return [
                'break_start' => optional($break->break_start)->format('H:i'),
                'break_end' => optional($break->break_end)->format('H:i'),
            ];
        })->toArray()
        : [];

    return view('attendance-detail', compact(
        'attendance',
        'user',
        'date',
        'breaks',
        'pendingRequest'
    ));
}

    public function update(Request $request, $date)
{
    $date = Carbon::parse($date);
    $user = auth()->user();

    $pendingRequest = \App\Models\AttendanceRequest::where('user_id', $user->id)
        ->where('target_date', $date)
        ->where('status', 'pending')
        ->exists();

    if ($pendingRequest) {
        // 承認待ちの間は更新せず戻す
        return redirect()->route('attendance.show', $date->format('Y-m-d'));
    }

    // AttendanceRequest として申請を作成
    \App\Models\AttendanceRequest::create([
        'user_id'     => $user->id,
        'target_date' => $date,
        'clock_in'    => $request->clock_in,
        'clock_out'   => $request->clock_out,
        'breaks'      => $request->breaks,
        'remark'      => $request->remark,
        'status'      => 'pending',
    ]);

    return redirect()->route('attendance.show', $date->format('Y-m-d'));
}
}
