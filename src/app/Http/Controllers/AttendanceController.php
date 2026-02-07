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
        // 表示する月（今月）
        $targetMonth = Carbon::now()->startOfMonth();

        // ① 月の日付一覧を作成
        $days = [];
        $start = $targetMonth->copy();
        $end   = $targetMonth->copy()->endOfMonth();

        while ($start <= $end) {
            $days[] = $start->copy();
            $start->addDay();
        }

        // ② 勤怠データ取得（その月分）
        $attendances = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->whereBetween('work_date', [
                $targetMonth->copy()->startOfMonth(),
                $targetMonth->copy()->endOfMonth(),
            ])
            ->get()
            ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));

        // ③ 日付 × 勤怠 を合成
        $calendar = [];

        foreach ($days as $day) {
            $attendance = $attendances->get($day->format('Y-m-d'));

        if ($attendance) {
            // --- 休憩時間合計（分） ---
            $breakMinutes = $attendance->breaks->sum(function ($break) {
                if (!$break->break_start || !$break->break_end) {
                    return 0;
                }

                return Carbon::parse($break->break_start)
                    ->diffInMinutes(Carbon::parse($break->break_end));
            });

            // --- 勤務時間（分） ---
            $workMinutes = 0;
            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes =
                    Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out))
                    - $breakMinutes;
            }

            // 表示用
            $attendance->break_time = sprintf(
                '%02d:%02d',
                intdiv($breakMinutes, 60),
                $breakMinutes % 60
            );

            $attendance->total_time = sprintf(
                '%02d:%02d',
                intdiv($workMinutes, 60),
                $workMinutes % 60
            );
        }

        $calendar[] = [
            'date'       => $day,
            'attendance' => $attendance,
        ];
    }

    return view('attendance-list', compact('calendar', 'targetMonth'));
    }
}
